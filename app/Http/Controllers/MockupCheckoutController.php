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
            'size' => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'mockup_image' => ['required', 'string'],
            'mockup_name' => ['nullable', 'string', 'max:120'],
            'mockup_type' => ['nullable', 'string', 'max:40'],
            'artwork_file' => ['nullable', 'file', 'max:20480'],
        ]);

        $imagePath = $this->storeMockupImage((string) $data['mockup_image']);
        $artworkPath = $request->hasFile('artwork_file')
            ? $this->storeArtworkFile($request->file('artwork_file'))
            : null;
        $mockupName = trim((string) ($data['mockup_name'] ?? 'Custom mockup'));
        $mockupType = Str::lower(trim((string) ($data['mockup_type'] ?? '')));
        $size = trim((string) $data['size']);
        $quantity = (int) $data['quantity'];
        $price = $this->priceForMockup($mockupType);
        $total = $price * $quantity;
        $invoice = DB::transaction(function () use ($data, $imagePath, $artworkPath, $mockupName, $mockupType, $size, $quantity, $price, $total) {
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
                'notes' => collect([
                    'Mockup design order',
                    "Mockup: {$mockupName}",
                    'Type: ' . ($mockupType ?: 'standard'),
                    "Size: {$size}",
                    "Quantity: {$quantity}",
                    'Mockup file: ' . Storage::disk('public')->url($imagePath),
                    $artworkPath ? 'Artwork file: ' . Storage::disk('public')->url($artworkPath) : null,
                ])->filter()->implode("\n"),
                'terms' => 'Please complete payment before production begins.',
            ]);

            $invoice->items()->create([
                'name' => 'Custom mockup design',
                'description' => "{$mockupName} | Size: {$size}",
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => $total,
                'position' => 0,
            ]);

            $invoice->load('items', 'payments');
            $invoice->recalculateTotals();
            $invoice->refreshStatus();
            $invoice->save();

            return $invoice;
        });

        $mockupAttachment = [
            'path' => Storage::disk('public')->path($imagePath),
            'name' => 'nai-prints-mockup.png',
            'mime' => 'image/png',
        ];
        $artworkAttachment = $artworkPath ? [
            'path' => Storage::disk('public')->path($artworkPath),
            'name' => basename($artworkPath),
            'mime' => Storage::disk('public')->mimeType($artworkPath) ?: 'application/octet-stream',
        ] : null;
        $supportEmail = 'info@naiprints.co.ke';
        $clientDelivery = $mailDispatch->sendToRecipients(
            [(string) $data['email']],
            fn () => new InvoiceMail($invoice, [$mockupAttachment]),
            [
                'event' => 'mockup.invoice_mail.client',
                'invoice_id' => $invoice->id,
            ]
        );
        $supportDelivery = $mailDispatch->sendToRecipients(
            [$supportEmail],
            fn () => new InvoiceMail($invoice, array_values(array_filter([$mockupAttachment, $artworkAttachment]))),
            [
                'event' => 'mockup.invoice_mail.support',
                'invoice_id' => $invoice->id,
            ]
        );
        $sent = collect([...$clientDelivery['sent'], ...$supportDelivery['sent']])->unique()->values()->all();
        $failed = collect([...$clientDelivery['failed'], ...$supportDelivery['failed']])->unique()->values()->all();

        return response()->json([
            'invoice_id' => $invoice->public_id,
            'invoice_url' => route('invoice.public', $invoice->public_id),
            'sent' => $sent,
            'failed' => $failed,
            'total' => $total,
            'message' => empty($failed)
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

    private function storeArtworkFile(?\Illuminate\Http\UploadedFile $file): string
    {
        if (!$file || !$file->isValid()) {
            abort(422, 'The artwork file is invalid.');
        }

        $extension = Str::lower((string) $file->getClientOriginalExtension());
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'ai', 'svg'];
        if (!in_array($extension, $allowed, true)) {
            abort(422, 'Artwork must be a PDF, JPEG, PNG, AI, or SVG file.');
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName) ?: 'artwork';
        $filename = $safeName . '-' . Str::uuid() . '.' . $extension;

        return $file->storeAs('mockup-orders/artwork/' . now()->format('Y/m'), $filename, 'public');
    }
}
