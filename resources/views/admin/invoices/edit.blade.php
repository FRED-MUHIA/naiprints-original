@extends('admin.layout')

@section('title', 'Edit Invoice')

@section('content')
    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.invoices._form', ['invoice' => $invoice])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Update Invoice</button>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
