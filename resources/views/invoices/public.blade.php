@php
    use Illuminate\Support\Facades\Storage;
    $logoUrl = $settings->logo_path ? Storage::url($settings->logo_path) : null;
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->number }} - Nai Prints</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 text-slate-900" style="--brand-red: {{ $settings->theme_color ?? '#e11d2e' }};">
    <div class="mx-auto max-w-4xl px-4 py-10">
        <div class="rounded-3xl bg-white p-8 shadow-xl">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="flex items-center gap-4">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Nai Prints" class="h-14 w-14 rounded-full object-cover">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand text-white text-lg font-bold">NP</div>
                    @endif
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Invoice</p>
                        <h1 class="text-2xl font-semibold">{{ $settings->business_name }}</h1>
                        <p class="text-sm text-slate-500">{{ $settings->phone }} {{ $settings->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">Invoice #</p>
                    <p class="text-lg font-semibold">{{ $invoice->number }}</p>
                    <p class="text-sm text-slate-500 mt-2">Date: {{ optional($invoice->issue_date)->format('M d, Y') }}</p>
                    <p class="text-sm text-slate-500">Due: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Bill To</p>
                    <p class="mt-2 text-lg font-semibold">{{ $invoice->client?->name }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client?->company_name }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client?->phone }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client?->email }}</p>
                    <p class="text-sm text-slate-500">{{ $invoice->client?->address }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Status</span>
                        <span class="font-semibold text-slate-800">{{ str_replace('_', ' ', $invoice->status) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-sm text-slate-500">
                        <span>Total</span>
                        <span class="font-semibold text-slate-800">KES {{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-sm text-slate-500">
                        <span>Paid</span>
                        <span class="font-semibold text-slate-800">KES {{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="mt-4 flex justify-between text-sm font-semibold text-slate-900">
                        <span>Balance</span>
                        <span>KES {{ number_format($invoice->balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                        <tr>
                            <th class="py-2">Item</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach ($invoice->items as $item)
                            <tr class="border-t border-slate-200">
                                <td class="py-3 font-semibold">{{ $item->name }}</td>
                                <td>{{ $item->description ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>KES {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">KES {{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-[1.4fr_0.6fr]">
                <div class="space-y-3 text-sm text-slate-500">
                    @if ($settings->payment_methods)
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Payment Methods</p>
                        <ul class="list-disc pl-5">
                            @foreach ($settings->payment_methods as $method)
                                <li>{{ $method }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($settings->footer_text)
                        <p>{{ $settings->footer_text }}</p>
                    @endif
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>KES {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between">
                        <span>Tax</span>
                        <span>KES {{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between">
                        <span>Discount</span>
                        <span>KES {{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    <div class="mt-3 flex justify-between text-base font-semibold text-slate-900">
                        <span>Total</span>
                        <span>KES {{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('invoice.public.pdf', $invoice->public_id) }}" class="btn-brand w-full justify-center">Download PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
