@extends('admin.layout')

@section('title', 'Edit Quotation')

@section('content')
    <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.quotations._form', ['quotation' => $quotation])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Update Quotation</button>
            <a href="{{ route('admin.quotations.show', $quotation) }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
