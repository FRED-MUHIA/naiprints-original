@php
    use Illuminate\Support\Facades\Storage;
    $accent = $settings->theme_color ?: '#e11d2e';
    $contactLines = $settings->documentContactLines();

    $logoUrl = null;
    if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
        $logoData = Storage::disk('public')->get($settings->logo_path);
        $logoMime = Storage::disk('public')->mimeType($settings->logo_path) ?? 'image/png';
        $logoUrl = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
    }

    $signatureUrl = null;
    if ($settings->signature_path && Storage::disk('public')->exists($settings->signature_path)) {
        $sigData = Storage::disk('public')->get($settings->signature_path);
        $sigMime = Storage::disk('public')->mimeType($settings->signature_path) ?? 'image/png';
        $signatureUrl = 'data:' . $sigMime . ';base64,' . base64_encode($sigData);
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->number }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; padding: 0; }
        .page { padding: 28px 32px; }
        .topbar { height: 6px; background: {{ $accent }}; margin: -28px -32px 18px -32px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 52px; height: 52px; border-radius: 999px; object-fit: cover; }
        .brand-name { font-size: 18px; font-weight: bold; }
        .muted { color: #6b7280; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 20px; letter-spacing: 1px; text-transform: uppercase; color: {{ $accent }}; }
        .doc-meta { margin-top: 6px; font-size: 11px; color: #6b7280; }
        .bill-table { width: 100%; border-collapse: separate; border-spacing: 12px; margin-top: 8px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #f9fafb; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #6b7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: {{ $accent }}; color: #ffffff; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; }
        .totals-table { width: 40%; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        .totals-table td { padding: 4px 0; border: 0; }
        .totals-table .total { font-weight: bold; font-size: 13px; }
        .signature { margin-top: 26px; display: flex; justify-content: flex-end; }
        .signature img { max-width: 160px; max-height: 60px; object-fit: contain; display: block; margin-left: auto; }
        .sig-line { border-top: 1px solid #d1d5db; width: 180px; text-align: center; padding-top: 6px; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 22px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar"></div>
        <table class="header-table">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="brand">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" class="logo" alt="Logo">
                        @else
                            <div class="logo" style="background: {{ $accent }}; color: #fff; display: flex; align-items: center; justify-content: center;">NP</div>
                        @endif
                        <div>
                            <div class="brand-name">{{ $settings->business_name }}</div>
                            @foreach ($contactLines as $line)
                                <div class="muted">{{ $line }}</div>
                            @endforeach
                        </div>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <div class="doc-title">
                        <h1>Quotation</h1>
                        <div class="doc-meta">Quotation #: {{ $quotation->number }}</div>
                        <div class="doc-meta">Date: {{ optional($quotation->issue_date)->format('M d, Y') }}</div>
                        <div class="doc-meta">Valid Until: {{ optional($quotation->valid_until)->format('M d, Y') ?? '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="bill-table">
            <tr>
                <td class="panel" style="width: 58%; vertical-align: top;">
                    <div class="section-title">Prepared For</div>
                    <div style="font-weight: bold;">{{ $quotation->client?->name }}</div>
                    <div>{{ $quotation->client?->company_name }}</div>
                    <div>{{ $quotation->client?->phone }}</div>
                    <div>{{ $quotation->client?->email }}</div>
                    <div>{{ $quotation->client?->address }}</div>
                </td>
                <td class="panel" style="width: 42%; vertical-align: top;">
                    <div class="section-title">Summary</div>
                    <div>Status: {{ ucfirst($quotation->status) }}</div>
                    <div>Total: KES {{ number_format($quotation->total, 2) }}</div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>KES {{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align:right;">KES {{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr><td>Subtotal</td><td style="text-align: right;">KES {{ number_format($quotation->subtotal, 2) }}</td></tr>
            <tr><td>Tax</td><td style="text-align: right;">KES {{ number_format($quotation->tax_amount, 2) }}</td></tr>
            <tr><td>Discount</td><td style="text-align: right;">KES {{ number_format($quotation->discount_amount, 2) }}</td></tr>
            <tr><td class="total">Total</td><td class="total" style="text-align: right;">KES {{ number_format($quotation->total, 2) }}</td></tr>
        </table>

        <div style="clear: both;"></div>

        <div class="signature">
            @if ($signatureUrl)
                <img src="{{ $signatureUrl }}" alt="Authorized Signature">
            @endif
            <div class="sig-line">Authorized Signature</div>
        </div>

        @if (!empty($quotation->terms))
            <div class="footer">
                <div class="section-title">Terms</div>
                <div style="white-space: pre-line;">{{ $quotation->terms }}</div>
            </div>
        @endif
        @if (!empty($quotation->notes))
            <div class="footer">
                <div class="section-title">Notes</div>
                <div style="white-space: pre-line;">{{ $quotation->notes }}</div>
            </div>
        @endif
        @if ($settings->footer_text)
            <div class="footer">{{ $settings->footer_text }}</div>
        @endif
    </div>
</body>
</html>
