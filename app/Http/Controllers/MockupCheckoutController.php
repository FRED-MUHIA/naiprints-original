<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\MailDispatchService;
use App\Support\DocumentSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MockupCheckoutController extends Controller
{
    private const DEFAULT_PRICE = 1000;

    private const PRICES = [
        'hoodie' => 2500,
        'polo' => 1200,
        'cap' => 550,
        'totebag' => 600,
    ];

    public function store(Request $request, MailDispatchService $mailDispatch): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'mockup_image' => ['required', 'string'],
            'mockup_name' => ['nullable', 'string', 'max:120'],
            'mockup_type' => ['nullable', 'string', 'max:40'],
        ]);

        $imagePath = $this->storeMockupImage((string) $data['mockup_image']);
        $mockupName = trim((string) ($data['mockup_name'] ?? 'Custom mockup'));
        $mockupType = Str::lower(trim((string) ($data['mockup_type'] ?? '')));
        $price = $this->priceForMockup($mockupType);
        $invoice = DB::transaction(function () use ($data, $imagePath, $mockupName, $mockupType, $price) {
            $client = Client::firstOrNew(['email' => $data['email']]);
            $client->fill([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? $client->phone,
            ]);
            $client->save();

            $sequence = DocumentSequence::next(Invoice::class);
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'sequence' => $sequence,
                'number' => 'INV-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'public_id' => (string) Str::uuid(),
                'issue_date' => now(),
                'due_date' => now()->addDays(7),
                'tax_rate' => null,
                'discount_amount' => 0,
                'notes' => "Mockup design order\nMockup: {$mockupName}\nType: " . ($mockupType ?: 'standard') . "\nMockup file: " . Storage::disk('public')->url($imagePath),
                'terms' => 'Please complete payment before production begins.',
            ]);

            $invoice->items()->create([
                'name' => 'Custom mockup design',
                'description' => $mockupName,
                'quantity' => 1,
                'unit_price' => $price,
                'total' => $price,
                'position' => 0,
            ]);

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            return $invoice;
        });

        $attachmentPath = Storage::disk('public')->path($imagePath);
        $supportEmail = 'info@naiprints.co.ke';
        $delivery = $mailDispatch->sendToRecipients(
            [(string) $data['email'], $supportEmail],
            fn () => new InvoiceMail($invoice, [
                [
                    'path' => $attachmentPath,
                    'name' => 'nai-prints-mockup.png',
                    'mime' => 'image/png',
                ],
            ]),
            [
                'event' => 'mockup.invoice_mail',
                'invoice_id' => $invoice->id,
            ]
        );

        return response()->json([
            'invoice_id' => $invoice->public_id,
            'invoice_url' => route('invoice.public', $invoice->public_id),
            'sent' => $delivery['sent'],
            'failed' => $delivery['failed'],
            'total' => $price,
            'message' => empty($delivery['failed'])
                ? 'Mockup added to cart and invoice emailed.'
                : 'Mockup added to cart and invoice created. Some emails failed to send.',
        ]);
    }

    private function priceForMockup(string $type): int
    {
        return self::PRICES[$type] ?? self::DEFAULT_PRICE;
    }

    private function storeMockupImage(string $dataUrl): string
    {
        if (!preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', $dataUrl, $matches)) {
            abort(422, 'The mockup image must be a PNG data URL.');
        }

        $binary = base64_decode($matches[1], true);
        if ($binary === false || strlen($binary) > 8 * 1024 * 1024) {
            abort(422, 'The mockup image is invalid or too large.');
        }

        $path = 'mockup-orders/' . now()->format('Y/m') . '/' . Str::uuid() . '.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
