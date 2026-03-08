@extends('admin.layout')

@section('title', 'Payments')

@section('content')
    <div class="admin-panel overflow-hidden p-2">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="font-semibold text-slate-700">
                            <a href="{{ route('admin.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->number }}</a>
                        </td>
                        <td>{{ $payment->invoice?->client?->display_name ?? '-' }}</td>
                        <td>{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                        <td>{{ $payment->method ?? '-' }}</td>
                        <td>KES {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="admin-empty">No payments recorded yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links() }}
@endsection
