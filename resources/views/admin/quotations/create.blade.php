@extends('admin.layout')

@section('title', 'Create Quotation')

@section('content')
    <form method="POST" action="{{ route('admin.quotations.store') }}" class="space-y-6">
        @csrf
        @include('admin.quotations._form')

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Quotation</button>
            <a href="{{ route('admin.quotations.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
