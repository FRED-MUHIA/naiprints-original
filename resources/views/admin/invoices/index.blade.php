@extends('admin.layout')

@section('title', 'Invoicing Dashboard')

@section('content')
    <div class="grid gap-4 md:grid-cols-4">
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['total'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Paid</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $stats['paid'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Partial</p>
            <p class="mt-2 text-3xl font-semibold text-amber-600">{{ $stats['partial'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Unpaid</p>
            <p class="mt-2 text-3xl font-semibold text-rose-600">{{ $stats['unpaid'] }}</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="admin-search">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search invoices..." class="admin-input">
                </div>
                <select name="status" class="admin-input border border-slate-200 rounded-xl px-3 py-2">
                    <option value="">All statuses</option>
                    <option value="paid" @selected($status === 'paid')>Paid</option>
                    <option value="partially_paid" @selected($status === 'partially_paid')>Partially Paid</option>
                    <option value="unpaid" @selected($status === 'unpaid')>Unpaid</option>
                </select>
                <button type="submit" class="admin-btn-outline">Filter</button>
                <a href="{{ route('admin.invoices.export', ['status' => $status]) }}" class="admin-btn-outline">Export CSV</a>
            </form>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.invoices.settings') }}" class="admin-btn-outline">Settings</a>
                <a href="{{ route('admin.invoices.trash') }}" class="admin-btn-outline">Trash</a>
                <a href="{{ route('admin.invoices.create') }}" class="admin-btn-primary">New Invoice</a>
            </div>
        </div>
    </div>

    <div class="admin-panel overflow-hidden p-2">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $invoice->number }}</td>
                        <td>{{ $invoice->client?->display_name ?? '-' }}</td>
                        <td>{{ optional($invoice->issue_date)->format('M d, Y') }}</td>
                        <td>{{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</td>
                        <td>
                            @php
                                $statusClass = $invoice->status === 'paid'
                                    ? 'admin-status-paid'
                                    : ($invoice->status === 'partially_paid' ? 'admin-status-partial' : 'admin-status-unpaid');
                            @endphp
                            <span class="admin-status admin-status-dot {{ $statusClass }}">
                                {{ str_replace('_', ' ', $invoice->status) }}
                            </span>
                        </td>
                        <td>KES {{ number_format($invoice->total, 2) }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="admin-btn-outline">View</a>
                                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn-outline border-rose-200 text-rose-600 hover:border-rose-400 hover:text-rose-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">No invoices yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $invoices->links() }}
@endsection
