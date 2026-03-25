@extends('admin.layout')

@section('title', 'Quotation Trash')

@section('content')
    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-slate-600">Deleted quotations can be restored or permanently removed.</p>
            <a href="{{ route('admin.quotations.index') }}" class="admin-btn-outline">Back to Quotations</a>
        </div>
    </div>

    <div class="admin-panel overflow-hidden p-2">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Quotation</th>
                    <th>Client</th>
                    <th>Deleted At</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                    <tr>
                        <td class="font-semibold text-slate-700">{{ $quotation->number }}</td>
                        <td>{{ $quotation->client?->display_name ?? '-' }}</td>
                        <td>{{ optional($quotation->deleted_at)->format('M d, Y') }}</td>
                        <td>KES {{ number_format($quotation->total, 2) }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.quotations.restore', $quotation->id) }}">
                                    @csrf
                                    <button type="submit" class="admin-btn-outline">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.quotations.force', $quotation->id) }}" onsubmit="return confirm('Permanently delete this quotation?');">
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
                        <td colspan="5">
                            <div class="admin-empty">Trash is empty.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $quotations->links() }}
@endsection
