@extends('admin.layout')

@section('title', 'Receipts')

@section('content')
    <div class="admin-panel overflow-hidden">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($receipts as $receipt)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $receipt->number }}</td>
                        <td>{{ $receipt->invoice?->number }}</td>
                        <td>{{ $receipt->invoice?->client?->display_name ?? '-' }}</td>
                        <td>{{ optional($receipt->payment_date)->format('M d, Y') }}</td>
                        <td>KES {{ number_format($receipt->amount, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('receipt.public', $receipt->public_id) }}" class="admin-btn-outline" target="_blank">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">No receipts yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $receipts->links() }}
@endsection
