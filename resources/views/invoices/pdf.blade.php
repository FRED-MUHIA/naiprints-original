@php
    use Illuminate\Support\Facades\Storage;
    $logoUrl = $settings->logo_path ? Storage::url($settings->logo_path) : null;
    $accent = $settings->theme_color ?: '#e11d2e';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { color: #111827; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 48px; height: 48px; border-radius: 999px; }
        .badge { color: #ffffff; background: {{ $accent }}; padding: 6px 12px; border-radius: 999px; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #6b7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { color: #6b7280; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; }
        .totals { margin-top: 16px; width: 40%; float: right; }
        .totals-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .totals-row strong { font-size: 13px; }
        .footer { margin-top: 24px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" class="logo" alt="Logo">
            @else
                <div class="logo" style="background: {{ $accent }}; color: #fff; display: flex; align-items: center; justify-content: center;">NP</div>
            @endif
            <div>
                <div class="section-title">Invoice</div>
                <div style="font-size: 16px; font-weight: bold;">{{ $settings->business_name }}</div>
                <div style="color: #6b7280;">{{ $settings->phone }} {{ $settings->email }}</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div class="badge">{{ $invoice->number }}</div>
            <div style="margin-top: 8px;">Date: {{ optional($invoice->issue_date)->format('M d, Y') }}</div>
            <div>Due: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; gap: 20px;">
        <div style="width: 50%;">
            <div class="section-title">Bill To</div>
            <div style="font-weight: bold;">{{ $invoice->client?->name }}</div>
            <div>{{ $invoice->client?->company_name }}</div>
            <div>{{ $invoice->client?->phone }}</div>
            <div>{{ $invoice->client?->email }}</div>
            <div>{{ $invoice->client?->address }}</div>
        </div>
        <div style="width: 40%;">
            <div class="section-title">Summary</div>
            <div>Total: KES {{ number_format($invoice->total, 2) }}</div>
            <div>Paid: KES {{ number_format($invoice->amount_paid, 2) }}</div>
            <div>Balance: KES {{ number_format($invoice->balance, 2) }}</div>
            <div>Status: {{ str_replace('_', ' ', $invoice->status) }}</div>
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

    @if ($settings->payment_methods)
        <div class="footer">
            <div class="section-title">Payment Methods</div>
            {{ implode(', ', $settings->payment_methods) }}
        </div>
    @endif
    @if ($settings->footer_text)
        <div class="footer">{{ $settings->footer_text }}</div>
    @endif
</body>
</html>
