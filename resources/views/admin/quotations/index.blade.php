@extends('admin.layout')

@section('title', 'Quotation Dashboard')

@section('content')
    <div class="grid gap-4 md:grid-cols-4">
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['total'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Draft</p>
            <p class="mt-2 text-3xl font-semibold text-slate-700">{{ $stats['draft'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Sent</p>
            <p class="mt-2 text-3xl font-semibold text-blue-700">{{ $stats['sent'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Accepted</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $stats['accepted'] }}</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="admin-search">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search quotations..." class="admin-input">
                </div>
                <select name="status" class="admin-input border border-slate-200 rounded-xl px-3 py-2">
                    <option value="">All statuses</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="sent" @selected($status === 'sent')>Sent</option>
                    <option value="accepted" @selected($status === 'accepted')>Accepted</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                </select>
                <button type="submit" class="admin-btn-outline">Filter</button>
                <a href="{{ route('admin.quotations.export', ['status' => $status]) }}" class="admin-btn-outline">Export CSV</a>
            </form>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.quotations.trash') }}" class="admin-btn-outline">Trash</a>
                <a href="{{ route('admin.quotations.create') }}" class="admin-btn-primary">New Quotation</a>
            </div>
        </div>
    </div>

    <div class="admin-panel overflow-hidden p-2">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Quotation</th>
                    <th>Client</th>
                    <th>Issue Date</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $quotation->number }}</td>
                        <td>{{ $quotation->client?->display_name ?? '-' }}</td>
                        <td>{{ optional($quotation->issue_date)->format('M d, Y') }}</td>
                        <td>{{ optional($quotation->valid_until)->format('M d, Y') ?? '-' }}</td>
                        <td>
                            @php
                                $statusClass = match ($quotation->status) {
                                    'accepted' => 'bg-emerald-100 text-emerald-700',
                                    'sent' => 'bg-blue-100 text-blue-700',
                                    'rejected' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="admin-status {{ $statusClass }}">
                                {{ str_replace('_', ' ', $quotation->status) }}
                            </span>
                        </td>
                        <td>KES {{ number_format($quotation->total, 2) }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.quotations.show', $quotation) }}" class="admin-btn-outline">View</a>
                                <form method="POST" action="{{ route('admin.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation? This cannot be undone.');">
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
                            <div class="admin-empty">No quotations yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $quotations->links() }}
@endsection
