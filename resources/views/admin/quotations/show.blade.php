@extends('admin.layout')

@section('title', 'Quotation ' . $quotation->number)

@section('content')
    <div class="admin-card space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</p>
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
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <a href="{{ route('admin.quotations.download', $quotation) }}" class="admin-btn-outline whitespace-nowrap">Download PDF</a>
                <form method="POST" action="{{ route('admin.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn-outline whitespace-nowrap border-rose-200 text-rose-600 hover:border-rose-400 hover:text-rose-700">
                        Delete
                    </button>
                </form>
                <a href="{{ route('admin.quotations.edit', $quotation) }}" class="admin-btn-primary whitespace-nowrap">Edit Quotation</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <div class="admin-panel p-6 space-y-4">
            <h2 class="text-lg font-semibold">Client</h2>
            <div class="grid gap-3 text-sm text-slate-600">
                <p><strong class="text-slate-800">{{ $quotation->client?->name }}</strong></p>
                <p>{{ $quotation->client?->company_name }}</p>
                <p>{{ $quotation->client?->phone }}</p>
                <p>{{ $quotation->client?->email }}</p>
                <p>{{ $quotation->client?->address }}</p>
            </div>
        </div>
        <div class="admin-panel p-6 space-y-3">
            <h2 class="text-lg font-semibold">Summary</h2>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Issue Date</span>
                <span>{{ optional($quotation->issue_date)->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Valid Until</span>
                <span>{{ optional($quotation->valid_until)->format('M d, Y') ?? '-' }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Status</span>
                <span>{{ ucfirst($quotation->status) }}</span>
            </div>
            <div class="flex justify-between text-sm font-semibold text-slate-800">
                <span>Total</span>
                <span>KES {{ number_format($quotation->total, 2) }}</span>
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
                @foreach ($quotation->items as $item)
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

    @if ($quotation->notes || $quotation->terms)
        <div class="admin-panel p-6 space-y-3">
            <h2 class="text-lg font-semibold">Quotation Details</h2>
            @if ($quotation->notes)
                <div class="text-sm text-slate-600 whitespace-pre-line">{{ $quotation->notes }}</div>
            @endif
            @if ($quotation->terms)
                <div class="text-sm text-slate-600 whitespace-pre-line">{{ $quotation->terms }}</div>
            @endif
        </div>
    @endif
@endsection
