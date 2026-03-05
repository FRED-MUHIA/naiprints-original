@extends('admin.layout')

@section('title', 'Edit Client')

@section('content')
    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.clients._form', ['client' => $client])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="admin-btn-primary">Update Client</button>
            <a href="{{ route('admin.clients.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
