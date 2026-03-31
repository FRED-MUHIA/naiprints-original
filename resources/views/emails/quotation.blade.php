<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin: 0 0 12px;">Your Quotation from Nai Prints</h2>
    <p style="margin: 0 0 8px;">Hello {{ $quotation->client?->name ?? 'there' }},</p>
    <p style="margin: 0 0 12px;">
        Your quotation <strong>{{ $quotation->number }}</strong> is ready.
        We have attached it as a PDF to this email.
    </p>
    <p style="margin: 0 0 8px;">Issue Date: <strong>{{ optional($quotation->issue_date)->format('M d, Y') }}</strong></p>
    <p style="margin: 0 0 8px;">Valid Until: <strong>{{ optional($quotation->valid_until)->format('M d, Y') ?? '-' }}</strong></p>
    <p style="margin: 0 0 8px;">Total: <strong>KES {{ number_format((float) $quotation->total, 2) }}</strong></p>
    <p style="margin: 16px 0 0;">Thank you for choosing Nai Prints.</p>
</div>
