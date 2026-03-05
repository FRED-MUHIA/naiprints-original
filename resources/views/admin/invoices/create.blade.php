@extends('admin.layout')

@section('title', 'Create Invoice')

@section('content')
    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-6">
        @csrf
        @include('admin.invoices._form')

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Invoice</button>
            <a href="{{ route('admin.invoices.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
