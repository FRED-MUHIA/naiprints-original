@php
    use Illuminate\Support\Facades\Storage;
    $logoDataUri = null;
    if (!empty($settings->logo_path) && Storage::disk('public')->exists($settings->logo_path)) {
        $logoPath = Storage::disk('public')->path($settings->logo_path);
        if (is_file($logoPath) && is_readable($logoPath)) {
            $logoContents = file_get_contents($logoPath);
            if ($logoContents !== false) {
                $logoMime = mime_content_type($logoPath) ?: 'image/png';
                $logoDataUri = 'data:' . $logoMime . ';base64,' . base64_encode($logoContents);
            }
        }
    }
    $accent = $settings->theme_color ?: '#e11d2e';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $receipt->number }}</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { color: #111827; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 48px; height: 48px; border-radius: 999px; }
        .badge { color: #ffffff; background: {{ $accent }}; padding: 6px 12px; border-radius: 999px; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #6b7280; margin-bottom: 6px; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; background: #f9fafb; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; }
        .footer { margin-top: 20px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
            @else
                <div class="logo" style="background: {{ $accent }}; color: #fff; display: flex; align-items: center; justify-content: center;">NP</div>
            @endif
            <div>
                <div class="section-title">Receipt</div>
                <div style="font-size: 16px; font-weight: bold;">{{ $settings->business_name }}</div>
                <div style="color: #6b7280;">{{ $settings->phone }} {{ $settings->email }}</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div class="badge">{{ $receipt->number }}</div>
            <div style="margin-top: 8px;">Date: {{ optional($receipt->payment_date)->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="row"><span>Invoice</span><span>{{ $receipt->invoice?->number }}</span></div>
        <div class="row"><span>Client</span><span>{{ $receipt->invoice?->client?->display_name }}</span></div>
        <div class="row"><span>Amount Paid</span><span>KES {{ number_format($receipt->amount, 2) }}</span></div>
        <div class="row"><span>Balance</span><span>KES {{ number_format($receipt->balance_after, 2) }}</span></div>
        <div class="row"><span>Method</span><span>{{ $receipt->method ?? '-' }}</span></div>
    </div>

    @if ($settings->footer_text)
        <div class="footer">{{ $settings->footer_text }}</div>
    @endif
</body>
</html>
