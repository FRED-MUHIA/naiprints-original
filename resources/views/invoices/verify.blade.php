<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
    <link rel="shortcut icon" href="/images/logo.svg">
    <title>Invoice Verification - Nai Prints</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="mx-auto max-w-3xl px-4 py-12">
        <div class="rounded-3xl bg-white p-8 shadow-xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-500">Verified</p>
                    <h1 class="text-2xl font-semibold">Invoice {{ $invoice->number }}</h1>
                    <p class="text-sm text-slate-500">Issued by {{ $settings->business_name }}</p>
                </div>
                <div class="rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                    Origin Confirmed
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 text-sm text-slate-600">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice Details</p>
                    <p class="mt-2"><strong>Issue Date:</strong> {{ optional($invoice->issue_date)->format('M d, Y') }}</p>
                    <p><strong>Due Date:</strong> {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</p>
                    <p><strong>Status:</strong> {{ str_replace('_', ' ', $invoice->status) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Amounts</p>
                    <p class="mt-2"><strong>Total:</strong> KES {{ number_format($invoice->total, 2) }}</p>
                    <p><strong>Paid:</strong> KES {{ number_format($invoice->amount_paid, 2) }}</p>
                    <p><strong>Balance:</strong> KES {{ number_format($invoice->balance, 2) }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Client</p>
                <p class="mt-2 font-semibold text-slate-800">{{ $invoice->client?->name }}</p>
                <p>{{ $invoice->client?->company_name }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('invoice.public', $invoice->public_id) }}" class="btn-brand">
                    View Invoice
                </a>
                <span class="text-slate-500">Verified on {{ now()->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
</body>
</html>
