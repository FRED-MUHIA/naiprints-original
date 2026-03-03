@extends('admin.layout')

@section('title', 'Edit Tag')

@section('content')
    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="admin-card">
            <h2 class="text-lg font-semibold">Tag details</h2>
            <div class="mt-4">
                <label class="text-sm font-semibold text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $tag->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update tag</button>
            <a href="{{ route('admin.tags.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
