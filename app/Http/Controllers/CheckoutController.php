<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\GlobalSection;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSetting;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use App\Support\PageDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:180'],
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

        $invoice = DB::transaction(function () use ($validated, $cleanItems, $subtotal) {
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

        $contactLinks = collect($footer->content['columns'] ?? [])
            ->flatMap(fn ($column) => $column['links'] ?? [])
            ->filter(fn ($link) => is_array($link))
            ->values();

        $contactEmail = $contactLinks->first(function ($link) {
            $label = (string) ($link['label'] ?? '');
            $url = (string) ($link['url'] ?? '');
            return str_contains($label, '@') || str_contains($url, '@');
        });

        $contactPhone = $contactLinks->first(function ($link) {
            $label = (string) ($link['label'] ?? '');
            return preg_match('/\\d{6,}/', $label);
        });

        $support = [
            'brand' => $footer->content['brand'] ?? 'Nai Prints',
            'email' => $contactEmail['label'] ?? config('mail.from.address', 'hello@naiprints.co.ke'),
            'phone' => $contactPhone['label'] ?? '0700 000 000',
            'logo_url' => $logoUrl,
        ];

        Mail::to($validated['email'])->send(new InvoiceMail($invoice));
        if ($support['email']) {
            Mail::to($support['email'])->send(new InvoiceMail($invoice));
        }

        if ($validated['payment'] === 'mobile_money') {
            $settings = InvoiceSetting::first() ?? new InvoiceSetting(InvoiceSetting::defaults());
            $mpesa = new MpesaService($settings);

            if (!$mpesa->isConfigured()) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'M-Pesa is not configured yet.']);
            }

            $phone = $mpesa->normalizePhone($validated['phone']);
            $callbackUrl = ($settings->mpesa_config['callback_url'] ?? null) ?: route('mpesa.stk.callback');

            try {
                $response = $mpesa->stkPush([
                    'Amount' => (float) $invoice->total,
                    'PartyA' => $phone,
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $invoice->number,
                    'TransactionDesc' => 'Invoice ' . $invoice->number,
                ]);
            } catch (\Throwable $exception) {
                return redirect()
                    ->route('invoice.public', $invoice->public_id)
                    ->withErrors(['payment' => 'M-Pesa request failed: ' . $exception->getMessage()]);
            }

            MpesaTransaction::create([
                'invoice_id' => $invoice->id,
                'type' => 'stk',
                'phone' => $phone,
                'amount' => $invoice->total,
                'reference' => $invoice->number,
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'status' => 'pending',
                'result_desc' => $response['ResponseDescription'] ?? null,
                'raw_request' => [
                    'source' => 'checkout',
                    'invoice_id' => $invoice->id,
                ],
            ]);

            return redirect()
                ->route('invoice.public', $invoice->public_id)
                ->with('success', 'Invoice created. STK prompt sent to your phone. Enter your M-Pesa PIN to complete payment.');
        }

        return redirect()
            ->route('invoice.public', $invoice->public_id)
            ->with('success', 'Invoice created and emailed. Please complete payment to confirm your order.');
    }
}
