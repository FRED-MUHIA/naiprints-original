@php
    $invoice = $receipt->invoice;
    $receiptLink = route('receipt.public', $receipt->public_id);
@endphp

<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin: 0 0 12px;">Payment Receipt from Nai Prints</h2>
    <p style="margin: 0 0 8px;">Hello {{ $invoice?->client?->name ?? 'there' }},</p>
    <p style="margin: 0 0 16px;">
        We have received your payment for invoice <strong>{{ $invoice?->number }}</strong>.
    </p>
    <p style="margin: 0 0 20px;">
        <a href="{{ $receiptLink }}" style="display: inline-block; padding: 10px 16px; background: #16a34a; color: #ffffff; text-decoration: none; border-radius: 999px;">
            View Receipt
        </a>
    </p>
    <p style="margin: 0 0 8px;">Receipt #: <strong>{{ $receipt->number }}</strong></p>
    <p style="margin: 0 0 8px;">Amount Paid: <strong>KES {{ number_format($receipt->amount, 2) }}</strong></p>
    <p style="margin: 0 0 8px;">Balance: <strong>KES {{ number_format($receipt->balance_after, 2) }}</strong></p>
    <p style="margin: 0;">Thank you for choosing Nai Prints.</p>
</div>
