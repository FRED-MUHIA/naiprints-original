@php
    $invoiceLink = route('invoice.public', $invoice->public_id);
@endphp

<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin: 0 0 12px;">Your Invoice from Nai Prints</h2>
    <p style="margin: 0 0 8px;">Hello {{ $invoice->client?->name ?? 'there' }},</p>
    <p style="margin: 0 0 16px;">
        Your invoice <strong>{{ $invoice->number }}</strong> is ready. You can view or download it using the link below.
    </p>
    <p style="margin: 0 0 20px;">
        <a href="{{ $invoiceLink }}" style="display: inline-block; padding: 10px 16px; background: #e11d2e; color: #ffffff; text-decoration: none; border-radius: 999px;">
            View Invoice
        </a>
    </p>
    <p style="margin: 0 0 8px;">Total: <strong>KES {{ number_format($invoice->total, 2) }}</strong></p>
    <p style="margin: 0;">Thank you for choosing Nai Prints.</p>
</div>
