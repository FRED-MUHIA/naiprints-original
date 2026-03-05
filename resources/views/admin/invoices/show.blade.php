@extends('admin.layout')

@section('title', 'Invoice ' . $invoice->number)

@section('content')
    <div class="admin-card space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</p>
                <span class="admin-status {{ $invoice->status === 'paid' ? 'admin-status-active' : ($invoice->status === 'partially_paid' ? 'admin-status-draft' : '') }}">
                    {{ str_replace('_', ' ', $invoice->status) }}
                </span>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('invoice.public', $invoice->public_id) }}" class="admin-btn-outline whitespace-nowrap" target="_blank">Public Link</a>
                    <a href="{{ route('admin.invoices.download', $invoice) }}" class="admin-btn-outline whitespace-nowrap">Download PDF</a>
                </div>
                <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="w-full sm:w-auto">
                    @csrf
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input type="email"
                               name="email"
                               placeholder="{{ $invoice->client?->email ?? 'Enter email address' }}"
                               class="admin-input border border-slate-200 rounded-xl px-3 py-2 text-sm sm:w-64 md:w-72">
                        <button type="submit" class="admin-btn-outline whitespace-nowrap">Send Email</button>
                    </div>
                </form>
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="admin-btn-primary whitespace-nowrap">Edit Invoice</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <div class="admin-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">Client</h2>
            <div class="grid gap-3 text-sm text-slate-600">
                <p><strong class="text-slate-800">{{ $invoice->client?->name }}</strong></p>
                <p>{{ $invoice->client?->company_name }}</p>
                <p>{{ $invoice->client?->phone }}</p>
                <p>{{ $invoice->client?->email }}</p>
                <p>{{ $invoice->client?->address }}</p>
            </div>
        </div>
        <div class="admin-panel p-6 space-y-3">
            <h2 class="text-lg font-semibold">Summary</h2>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Issue Date</span>
                <span>{{ optional($invoice->issue_date)->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Due Date</span>
                <span>{{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Total</span>
                <span>KES {{ number_format($invoice->total, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Paid</span>
                <span>KES {{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm font-semibold text-slate-800">
                <span>Balance</span>
                <span>KES {{ number_format($invoice->balance, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="admin-panel p-6 overflow-hidden">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $item->name }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>KES {{ number_format($item->unit_price, 2) }}</td>
                        <td>KES {{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($invoice->notes || $invoice->terms)
        <div class="admin-panel p-6 space-y-3">
            <h2 class="text-lg font-semibold">Order Details</h2>
            @if ($invoice->notes)
                <div class="text-sm text-slate-600 whitespace-pre-line">{{ $invoice->notes }}</div>
            @endif
            @if ($invoice->terms)
                <div class="text-sm text-slate-600 whitespace-pre-line">{{ $invoice->terms }}</div>
            @endif
        </div>
    @endif

    @php
        $mpesa = $settings->mpesa_config ?? [];
        $mpesaEnabled = !empty($mpesa['consumer_key']) && !empty($mpesa['consumer_secret']) && !empty($mpesa['shortcode']);
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="admin-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">Record Payment</h2>
            <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Amount
                    <input type="number" step="0.01" min="0.01" name="amount" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Payment Date
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Method
                    <select name="method" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                        <option value="">Select method</option>
                        @foreach (($settings->payment_methods ?? []) as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600">
                    Reference
                    <input type="text" name="reference" class="admin-input border border-slate-200 rounded-xl px-4 py-2">
                </label>
                <label class="space-y-2 text-sm font-semibold text-slate-600 md:col-span-2">
                    Note
                    <textarea name="note" rows="3" class="admin-input border border-slate-200 rounded-xl px-4 py-2"></textarea>
                </label>
                <div class="md:col-span-2">
                    <button type="submit" class="admin-btn-primary">Record Payment</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="admin-panel p-6 space-y-4">
                <h2 class="text-lg font-semibold">M-Pesa</h2>
                @if ($mpesaEnabled)
                    <form method="POST" action="{{ route('admin.invoices.mpesa.stk', $invoice) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="space-y-2 text-sm font-semibold text-slate-600">
                            Phone
                            <input type="text" name="phone" value="{{ old('phone', $invoice->client?->phone) }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                        </label>
                        <label class="space-y-2 text-sm font-semibold text-slate-600">
                            Amount
                            <input type="number" step="0.01" min="1" name="amount" value="{{ number_format($invoice->balance, 2, '.', '') }}" class="admin-input border border-slate-200 rounded-xl px-4 py-2" required>
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="admin-btn-outline">Send STK Push</button>
                        </div>
                    </form>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        <p><strong class="text-slate-800">PayBill:</strong> {{ $mpesa['shortcode'] }}</p>
                        <p><strong class="text-slate-800">Account Reference:</strong> {{ $invoice->number }}</p>
                    </div>
                    @if ($invoice->mpesaTransactions->isNotEmpty())
                        <div class="space-y-2 text-sm text-slate-600">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Recent M-Pesa</p>
                            @foreach ($invoice->mpesaTransactions->take(3) as $transaction)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">KES {{ number_format($transaction->amount ?? 0, 2) }}</p>
                                        <p class="text-xs text-slate-500">{{ strtoupper($transaction->type) }} · {{ $transaction->status }}</p>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $transaction->receipt_number ?? $transaction->checkout_request_id }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="admin-empty">M-Pesa is not configured yet. Add your API keys in Settings.</div>
                @endif
            </div>

            <div class="admin-panel p-6 space-y-4">
                <h2 class="text-lg font-semibold">Payments & Receipts</h2>
                <div class="space-y-3 text-sm text-slate-600">
                    @forelse ($invoice->payments as $payment)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">KES {{ number_format($payment->amount, 2) }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($payment->payment_date)->format('M d, Y') }} · {{ $payment->method ?? 'Payment' }}</p>
                                </div>
                                @if ($payment->receipt)
                                    <a href="{{ route('receipt.public', $payment->receipt->public_id) }}" class="admin-btn-outline" target="_blank">Receipt</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty">No payments recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
