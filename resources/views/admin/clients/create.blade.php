@extends('admin.layout')

@section('title', 'Add Client')

@section('content')
    <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-6">
        @csrf
        @include('admin.clients._form')

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Save Client</button>
            <a href="{{ route('admin.clients.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
