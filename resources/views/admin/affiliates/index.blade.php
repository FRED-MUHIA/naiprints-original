@extends('admin.layout')

@section('title', 'Affiliates')

@section('content')
    <section class="admin-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Affiliate settings</h2>
                <p class="mt-1 text-sm text-slate-500">Default commission applies unless an affiliate has a custom rate.</p>
            </div>
            <a class="admin-btn-outline" href="{{ route('admin.affiliates.export') }}">Export CSV</a>
        </div>
        <form method="POST" action="{{ route('admin.affiliates.settings') }}" class="mt-5 grid gap-4 md:grid-cols-4">
            @csrf
            <label class="grid gap-2 text-sm font-semibold">
                Type
                <select class="admin-input" name="commission_type">
                    <option value="percentage" @selected($settings->commission_type === 'percentage')>Percentage</option>
                    <option value="fixed" @selected($settings->commission_type === 'fixed')>Fixed</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Value
                <input class="admin-input" name="commission_value" type="number" step="0.01" value="{{ $settings->commission_value }}">
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Minimum payout
                <input class="admin-input" name="minimum_payout_amount" type="number" step="0.01" value="{{ $settings->minimum_payout_amount }}">
            </label>
            <div class="flex items-end">
                <button class="admin-btn-primary" type="submit">Save settings</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold">Affiliate accounts</h2>
            <form class="flex gap-2" method="GET">
                <select class="admin-input" name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'approved', 'rejected'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="text-xs uppercase tracking-[0.2em] text-slate-400">
                    <tr><th class="py-3">Affiliate</th><th>Status</th><th>Code</th><th>Clicks</th><th>Orders</th><th>Custom rate</th><th>Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($affiliates as $affiliate)
                        <tr>
                            <td class="py-3">
                                <a class="font-semibold text-brand" href="{{ route('admin.affiliates.show', $affiliate) }}">{{ $affiliate->full_name }}</a>
                                <div class="text-xs text-slate-500">{{ $affiliate->email }} · {{ $affiliate->phone }}</div>
                            </td>
                            <td>{{ ucfirst($affiliate->status) }}</td>
                            <td>{{ $affiliate->referral_code ?: '-' }}</td>
                            <td>{{ $affiliate->clicks_count }}</td>
                            <td>{{ $affiliate->invoices_count }}</td>
                            <td>{{ $affiliate->commission_type ? ucfirst($affiliate->commission_type) . ' ' . $affiliate->commission_value : 'Default' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @if ($affiliate->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}">@csrf<button class="admin-btn-primary" type="submit">Approve</button></form>
                                    @endif
                                    @if ($affiliate->status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.affiliates.reject', $affiliate) }}">@csrf<button class="admin-btn-outline" type="submit">Reject</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-slate-500" colspan="7">No affiliates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $affiliates->links() }}</div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="admin-card">
            <h2 class="text-xl font-semibold">Pending commissions</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($pendingCommissions as $commission)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                            <span><strong>{{ $commission->affiliate?->full_name }}</strong> · {{ $commission->invoice?->number }} · {{ $commission->product?->name ?? 'Product line' }} · KES {{ number_format((float) $commission->amount, 2) }}</span>
                            <div class="flex gap-2">
                                @foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $state => $label)
                                    <form method="POST" action="{{ route('admin.affiliate-commissions.update', $commission) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $state }}">
                                        <button class="{{ $state === 'approved' ? 'admin-btn-primary' : 'admin-btn-outline' }}" type="submit">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No pending commissions.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-card">
            <h2 class="text-xl font-semibold">Payout requests</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($pendingPayouts as $payout)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="text-sm">
                            <strong>{{ $payout->affiliate?->full_name }}</strong> requested KES {{ number_format((float) $payout->amount, 2) }}
                            <div class="text-xs text-slate-500">{{ $payout->payment_method }} · {{ $payout->payment_details }}</div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'paid' => 'Mark paid'] as $state => $label)
                                <form method="POST" action="{{ route('admin.affiliate-payouts.update', $payout) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $state }}">
                                    <button class="{{ $state === 'approved' || $state === 'paid' ? 'admin-btn-primary' : 'admin-btn-outline' }}" type="submit">{{ $label }}</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No pending payouts.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
