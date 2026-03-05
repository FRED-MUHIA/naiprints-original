@php
    use Illuminate\Support\Facades\Storage;
    $logoUrl = $settings->logo_path ? Storage::url($settings->logo_path) : null;
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipt->number }} - Receipt</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 text-slate-900" style="--brand-red: {{ $settings->theme_color ?? '#e11d2e' }};">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="flex items-center gap-4">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Nai Prints" class="h-14 w-14 rounded-full object-cover">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand text-white text-lg font-bold">NP</div>
                    @endif
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Receipt</p>
                        <h1 class="text-2xl font-semibold">{{ $settings->business_name }}</h1>
                        <p class="text-sm text-slate-500">{{ $settings->phone }} {{ $settings->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">Receipt #</p>
                    <p class="text-lg font-semibold">{{ $receipt->number }}</p>
                    <p class="text-sm text-slate-500 mt-2">Date: {{ optional($receipt->payment_date)->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                <div class="flex justify-between">
                    <span>Invoice</span>
                    <span class="font-semibold text-slate-800">{{ $receipt->invoice?->number }}</span>
                </div>
                <div class="mt-2 flex justify-between">
                    <span>Client</span>
                    <span class="font-semibold text-slate-800">{{ $receipt->invoice?->client?->display_name }}</span>
                </div>
                <div class="mt-2 flex justify-between">
                    <span>Amount Paid</span>
                    <span class="font-semibold text-slate-800">KES {{ number_format($receipt->amount, 2) }}</span>
                </div>
                <div class="mt-2 flex justify-between">
                    <span>Balance</span>
                    <span class="font-semibold text-slate-800">KES {{ number_format($receipt->balance_after, 2) }}</span>
                </div>
                <div class="mt-2 flex justify-between">
                    <span>Payment Method</span>
                    <span class="font-semibold text-slate-800">{{ $receipt->method ?? '-' }}</span>
                </div>
            </div>

            @if ($settings->footer_text)
                <p class="mt-6 text-sm text-slate-500">{{ $settings->footer_text }}</p>
            @endif

            <div class="mt-6">
                <a href="{{ route('receipt.public.pdf', $receipt->public_id) }}" class="btn-brand w-full justify-center">Download PDF</a>
            </div>
        </div>
    </div>
</body>
</html>
