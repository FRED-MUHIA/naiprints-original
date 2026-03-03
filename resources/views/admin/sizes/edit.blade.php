@extends('admin.layout')

@section('title', 'Edit Size')

@section('content')
    <form method="POST" action="{{ route('admin.sizes.update', $size) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="admin-card">
            <h2 class="text-lg font-semibold">Size details</h2>
            <div class="mt-4">
                <label class="text-sm font-semibold text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $size->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update size</button>
            <a href="{{ route('admin.sizes.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
