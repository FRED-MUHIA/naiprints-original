<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Mail\OrderPlaced;
use App\Models\Client;
use App\Models\GlobalSection;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Services\MailDispatchService;
use App\Services\MpesaService;
use App\Support\PageDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function submit(Request $request, MailDispatchService $mailDispatch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:60'],
            'address' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment' => ['required', 'string', 'max:40'],
            'cart_items' => ['required', 'string'],
            'cart_subtotal' => ['nullable', 'numeric', 'min:0'],
            'cart_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = json_decode($validated['cart_items'], true);
        if (!is_array($items)) {
            return back()->withErrors(['cart_items' => 'We could not read your cart items.'])->withInput();
        }

        $cleanItems = collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function ($item) {
                $qty = (int) ($item['qty'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                return [
                    'id' => (string) ($item['id'] ?? ''),
                    'name' => trim((string) ($item['name'] ?? 'Product')),
                    'price' => max(0, $price),
                    'qty' => max(0, $qty),
                    'image' => trim((string) ($item['image'] ?? '')),
                    'url' => trim((string) ($item['url'] ?? '')),
                    'color' => trim((string) ($item['color'] ?? '')),
                    'size' => trim((string) ($item['size'] ?? '')),
                ];
            })
            ->filter(fn ($item) => $item['qty'] > 0 && $item['name'] !== '')
            ->values();

        if ($cleanItems->isEmpty()) {
            return back()->withErrors(['cart_items' => 'Your cart is empty.'])->withInput();
        }

        $subtotal = $cleanItems->reduce(
            fn ($carry, $item) => $carry + ($item['price'] * $item['qty']),
            0.0
        );

        $invoice = DB::transaction(function () use ($validated, $cleanItems) {
            $client = Client::firstOrNew(['email' => $validated['email']]);
            $client->fill([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => trim($validated['address'] . ', ' . $validated['city']),
            ]);
            $client->save();

            $sequence = (int) Invoice::max('sequence') + 1;
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'sequence' => $sequence,
                'number' => 'INV-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'public_id' => (string) Str::uuid(),
                'issue_date' => now(),
                'due_date' => now()->addDays(7),
                'tax_rate' => null,
                'discount_amount' => 0,
                'notes' => trim("Checkout order\nAddress: {$validated['address']}\nCity: {$validated['city']}\nNotes: " . ($validated['notes'] ?? '')),
                'terms' => null,
            ]);

            $items = $cleanItems->values()->map(function ($item, $index) {
                $descriptionParts = [];
                if (!empty($item['color'])) {
                    $descriptionParts[] = 'Color: ' . $item['color'];
                }
                if (!empty($item['size'])) {
                    $descriptionParts[] = 'Size: ' . $item['size'];
                }
                $description = $descriptionParts ? implode(', ', $descriptionParts) : null;
                $qty = (float) $item['qty'];
                $unitPrice = (float) $item['price'];

                return [
                    'name' => $item['name'],
                    'description' => $description,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => round($qty * $unitPrice, 2),
                    'position' => $index,
                ];
            });

            $invoice->items()->createMany($items->all());

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            return $invoice;
        });

        $header = GlobalSection::ensure('header', PageDefaults::header());
        $footer = GlobalSection::ensure('footer', PageDefaults::footer());
        $logoPath = $header->content['logo_url'] ?? '/images/logo.svg';
        $logoUrl = url($logoPath ?: '/images/logo.svg');
        $support = $mailDispatch->resolveSupportContact($footer->content ?? [], $logoUrl);
        $clientEmail = $mailDispatch->normalizeEmail((string) ($validated['email'] ?? ''));
        $supportEmail = $mailDispatch->normalizeEmail((string) ($support['email'] ?? ''));
        $orderPayload = $this->buildOrderPayload($invoice, $validated, $cleanItems->all(), (float) $subtotal);

        $invoiceDelivery = $mailDispatch->sendToRecipients(
            [$clientEmail, $supportEmail],
            fn () => new InvoiceMail($invoice),
            [
                'event' => 'checkout.invoice_mail',
                'invoice_id' => $invoice->id,
            ]
        );

        $orderDelivery = $mailDispatch->sendToRecipients(
            [$clientEmail, $supportEmail],
            fn () => new OrderPlaced($orderPayload, $support),
            [
                'event' => 'checkout.order_placed_mail',
                'invoice_id' => $invoice->id,
            ]
        );

        $emailFailures = collect([
            ...$invoiceDelivery['failed'],
            ...$orderDelivery['failed'],
        ])->unique()->values()->all();

        if (!empty($emailFailures)) {
            session()->flash('warning', 'Order saved, but one or more confirmation emails failed to send.');
        }

        if ($validated['payment'] === 'mobile_money') {
            $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
            $mpesa = new MpesaService($settings);

            if (!$mpesa->isConfigured()) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'M-Pesa is not configured yet.']);
            }

            try {
                $phone = method_exists($mpesa, 'validatePhone')
                    ? $mpesa->validatePhone($validated['phone'])
                    : (method_exists($mpesa, 'normalizePhone')
                        ? $mpesa->normalizePhone($validated['phone'])
                        : (function (string $raw): string {
                            $digits = preg_replace('/\D+/', '', $raw) ?? '';
                            if ($digits === '') {
                                return '';
                            }
                            if (str_starts_with($digits, '0') && strlen($digits) === 10) {
                                return '254' . substr($digits, 1);
                            }
                            if (str_starts_with($digits, '7') && strlen($digits) === 9) {
                                return '254' . $digits;
                            }

                            return $digits;
                        })((string) $validated['phone']));
            } catch (\Throwable $exception) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => $exception->getMessage()]);
            }

            if (!preg_match('/^254[17]\d{8}$/', (string) $phone)) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'Invalid M-Pesa phone format. Use 07XXXXXXXX/01XXXXXXXX.']);
            }

            $rawCallbackUrl = (string) (($settings->mpesa_config['callback_url'] ?? null) ?: secure_url('/mpesa/stk/callback'));
            $callbackUrl = method_exists($mpesa, 'sanitizeCallbackUrl')
                ? $mpesa->sanitizeCallbackUrl($rawCallbackUrl)
                : trim($rawCallbackUrl);

            $amount = method_exists($mpesa, 'normalizeAmount')
                ? $mpesa->normalizeAmount((float) $invoice->total)
                : max(1, (int) round((float) $invoice->total));
            $hasRecentPending = MpesaTransaction::query()
                ->where('invoice_id', $invoice->id)
                ->where('phone', $phone)
                ->where('type', 'stk')
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subMinutes(2))
                ->exists();

            if ($hasRecentPending) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'An STK prompt was already sent in the last 2 minutes. Complete it on your phone, then retry if needed.']);
            }

            try {
                $response = $mpesa->stkPush([
                    'Amount' => $amount,
                    'PartyA' => $phone,
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $invoice->number,
                    'TransactionDesc' => 'Invoice payment',
                ]);
            } catch (\Throwable $exception) {
                logger()->error('Checkout STK push failed', [
                    'invoice_id' => $invoice->id,
                    'phone' => $phone,
                    'amount' => $amount,
                    'callback_url' => $callbackUrl,
                    'error' => $exception->getMessage(),
                ]);
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'M-Pesa request failed: ' . $exception->getMessage()]);
            }

            MpesaTransaction::create([
                'invoice_id' => $invoice->id,
                'type' => 'stk',
                'phone' => $phone,
                'amount' => $amount,
                'reference' => $invoice->number,
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'status' => 'pending',
                'result_desc' => $response['ResponseDescription'] ?? null,
                'raw_request' => [
                    'source' => 'checkout',
                    'invoice_id' => $invoice->id,
                    'order' => $orderPayload,
                    'support' => $support,
                    'order_email_sent_to' => $orderDelivery['sent'],
                    'order_email_failed_to' => $orderDelivery['failed'],
                    'order_email_sent' => empty($orderDelivery['failed']),
                    'invoice_email_sent_to' => $invoiceDelivery['sent'],
                    'invoice_email_failed_to' => $invoiceDelivery['failed'],
                ],
            ]);

            $mobileMoneyMessage = empty($emailFailures)
                ? 'Invoice created. STK prompt sent to your phone. Enter your M-Pesa PIN to complete payment.'
                : 'Invoice created and STK prompt sent, but one or more email notifications failed.';

            return redirect()
                ->route('invoice.public', $invoice->public_id)
                ->with('success', $mobileMoneyMessage);
        }

        $checkoutMessage = empty($emailFailures)
            ? 'Invoice created and emailed. Please complete payment to confirm your order.'
            : 'Invoice created. Some confirmation emails failed to send.';

        return redirect()
            ->route('invoice.public', $invoice->public_id)
            ->with('success', $checkoutMessage);
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function buildOrderPayload(Invoice $invoice, array $validated, array $items, float $subtotal): array
    {
        return [
            'number' => $invoice->number,
            'invoice_public_id' => $invoice->public_id,
            'invoice_url' => route('invoice.public', $invoice->public_id),
            'payment_method' => $validated['payment'] ?? null,
            'customer' => [
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ],
            'shipping' => [
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
            'items' => collect($items)
                ->map(function (array $item) {
                    return [
                        'id' => $item['id'] ?? null,
                        'name' => $item['name'] ?? null,
                        'price' => (float) ($item['price'] ?? 0),
                        'qty' => (float) ($item['qty'] ?? 0),
                        'color' => $item['color'] ?? null,
                        'size' => $item['size'] ?? null,
                    ];
                })
                ->values()
                ->all(),
            'subtotal' => round($subtotal, 2),
            'total' => round((float) $invoice->total, 2),
            'placed_at' => now()->toIso8601String(),
        ];
    }
}
