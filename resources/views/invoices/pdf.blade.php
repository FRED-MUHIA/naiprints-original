@php
    use Illuminate\Support\Facades\Storage;
    $logoUrl = $settings->logo_path ? Storage::url($settings->logo_path) : null;
    $signatureUrl = $settings->signature_path ? Storage::url($settings->signature_path) : null;
    $accent = $settings->theme_color ?: '#e11d2e';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; padding: 0; }
        .page { padding: 28px 32px; }
        .topbar { height: 6px; background: {{ $accent }}; margin: -28px -32px 18px -32px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 20px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 52px; height: 52px; border-radius: 999px; object-fit: cover; }
        .brand-name { font-size: 18px; font-weight: bold; }
        .muted { color: #6b7280; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 20px; letter-spacing: 1px; text-transform: uppercase; color: {{ $accent }}; }
        .doc-meta { margin-top: 6px; font-size: 11px; color: #6b7280; }
        .bill-wrap { display: flex; justify-content: space-between; gap: 20px; margin-top: 10px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #f9fafb; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #6b7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: {{ $accent }}; color: #ffffff; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; }
        .totals { margin-top: 16px; width: 40%; float: right; }
        .totals-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .totals-row strong { font-size: 13px; }
        .balance { background: #111827; color: #ffffff; padding: 8px 12px; border-radius: 8px; font-weight: bold; text-align: right; }
        .qr { width: 90px; height: 90px; margin-top: 6px; }
        .signature { margin-top: 26px; display: flex; justify-content: flex-end; }
        .signature img { max-width: 160px; max-height: 60px; object-fit: contain; display: block; margin-left: auto; }
        .sig-line { border-top: 1px solid #d1d5db; width: 180px; text-align: center; padding-top: 6px; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 22px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar"></div>
        <div class="header">
            <div class="brand">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" class="logo" alt="Logo">
                @else
                    <div class="logo" style="background: {{ $accent }}; color: #fff; display: flex; align-items: center; justify-content: center;">NP</div>
                @endif
                <div>
                    <div class="brand-name">{{ $settings->business_name }}</div>
                    <div class="muted">{{ $settings->phone }} {{ $settings->email }}</div>
                    <div class="muted">{{ $settings->website }}</div>
                </div>
            </div>
            <div class="doc-title">
                <h1>Invoice</h1>
                <div class="doc-meta">Invoice #: {{ $invoice->number }}</div>
                <div class="doc-meta">Date: {{ optional($invoice->issue_date)->format('M d, Y') }}</div>
                <div class="doc-meta">Due: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</div>
            </div>
        </div>

        <div class="bill-wrap">
            <div style="width: 54%;" class="panel">
                <div class="section-title">Bill To</div>
                <div style="font-weight: bold;">{{ $invoice->client?->name }}</div>
                <div>{{ $invoice->client?->company_name }}</div>
                <div>{{ $invoice->client?->phone }}</div>
                <div>{{ $invoice->client?->email }}</div>
                <div>{{ $invoice->client?->address }}</div>
            </div>
            <div style="width: 40%;" class="panel">
                <div class="section-title">Summary</div>
                <div>Total: KES {{ number_format($invoice->total, 2) }}</div>
                <div>Paid: KES {{ number_format($invoice->amount_paid, 2) }}</div>
                <div>Balance: KES {{ number_format($invoice->balance, 2) }}</div>
                <div>Status: {{ str_replace('_', ' ', $invoice->status) }}</div>
                <div class="balance" style="margin-top: 8px;">Balance Due: KES {{ number_format($invoice->balance, 2) }}</div>
                @if (!empty($qrUrl))
                    <div style="margin-top: 12px;">
                        <div class="section-title">Verify Invoice</div>
                        <img src="{{ $qrUrl }}" class="qr" alt="Verify invoice QR">
                        <div style="font-size: 10px; color: #6b7280;">Scan to verify invoice origin.</div>
                    </div>
                @endif
            </div>
        </div>

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
            @foreach ($invoice->items as $item)
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

    <div class="totals">
        <div class="totals-row"><span>Subtotal</span><span>KES {{ number_format($invoice->subtotal, 2) }}</span></div>
        <div class="totals-row"><span>Tax</span><span>KES {{ number_format($invoice->tax_amount, 2) }}</span></div>
        <div class="totals-row"><span>Discount</span><span>KES {{ number_format($invoice->discount_amount, 2) }}</span></div>
        <div class="totals-row"><strong>Total</strong><strong>KES {{ number_format($invoice->total, 2) }}</strong></div>
    </div>

    <div style="clear: both;"></div>

        <div class="signature">
            @if ($signatureUrl)
                <img src="{{ $signatureUrl }}" alt="Authorized Signature">
            @endif
            <div class="sig-line">Authorized Signature</div>
        </div>

        @if ($settings->payment_methods)
            <div class="footer">
                <div class="section-title">Payment Methods</div>
                {{ implode(', ', $settings->payment_methods) }}
            </div>
        @endif
        @if ($settings->footer_text)
            <div class="footer">{{ $settings->footer_text }}</div>
        @endif
    </div>
</body>
</html>
