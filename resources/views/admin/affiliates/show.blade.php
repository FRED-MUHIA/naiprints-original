@extends('admin.layout')

@section('title', $affiliate->full_name)

@section('content')
    <section class="admin-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">{{ $affiliate->email }} · {{ $affiliate->phone }}</p>
                <p class="mt-2 text-sm">Status: <strong>{{ ucfirst($affiliate->status) }}</strong></p>
                <p class="mt-1 text-sm">Referral link: <strong>{{ $affiliate->referral_code ? $affiliate->referral_url : 'Generated on approval' }}</strong></p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}">@csrf<button class="admin-btn-primary" type="submit">Approve</button></form>
                <form method="POST" action="{{ route('admin.affiliates.reject', $affiliate) }}">@csrf<button class="admin-btn-outline" type="submit">Reject</button></form>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.affiliates.update', $affiliate) }}" class="mt-6 grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')
            <label class="grid gap-2 text-sm font-semibold">
                Custom commission type
                <select class="admin-input" name="commission_type">
                    <option value="">Use default</option>
                    <option value="percentage" @selected($affiliate->commission_type === 'percentage')>Percentage</option>
                    <option value="fixed" @selected($affiliate->commission_type === 'fixed')>Fixed</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Custom commission value
                <input class="admin-input" name="commission_value" type="number" step="0.01" value="{{ $affiliate->commission_value }}">
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Payment method
                <input class="admin-input" name="payment_method" value="{{ $affiliate->payment_method }}">
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Payment details
                <textarea class="admin-input" name="payment_details" rows="3">{{ $affiliate->payment_details }}</textarea>
            </label>
            <div class="md:col-span-2">
                <button class="admin-btn-primary" type="submit">Save affiliate</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 md:grid-cols-4">
        @foreach ([
            'Clicks' => $affiliate->clicks_count,
            'Orders' => $affiliate->invoices_count,
            'Pending' => 'KES ' . number_format((float) $affiliate->commissions->where('status', 'pending')->sum('amount'), 2),
            'Approved' => 'KES ' . number_format((float) $affiliate->commissions->where('status', 'approved')->sum('amount'), 2),
        ] as $label => $value)
            <article class="admin-card">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $label }}</p>
                <p class="mt-3 text-2xl font-bold">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="admin-card">
        <h2 class="text-xl font-semibold">Commissions</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="text-xs uppercase tracking-[0.2em] text-slate-400">
                    <tr><th class="py-3">Invoice</th><th>Product</th><th>Customer</th><th>Product total</th><th>Commission</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($affiliate->commissions as $commission)
                        <tr>
                            <td class="py-3">{{ $commission->invoice?->number }}</td>
                            <td>{{ $commission->product?->name ?? $commission->invoiceItem?->name ?? '-' }}</td>
                            <td>{{ $commission->invoice?->client?->display_name }}</td>
                            <td>KES {{ number_format((float) $commission->order_total, 2) }}</td>
                            <td>KES {{ number_format((float) $commission->amount, 2) }}</td>
                            <td>{{ ucfirst($commission->status) }}</td>
                            <td class="flex flex-wrap gap-2 py-2">
                                @foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'paid' => 'Paid'] as $state => $label)
                                    <form method="POST" action="{{ route('admin.affiliate-commissions.update', $commission) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $state }}">
                                        <button class="admin-btn-outline" type="submit">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-slate-500" colspan="7">No commissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
