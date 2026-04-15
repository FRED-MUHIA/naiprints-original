@extends('admin.layout')

@section('title', 'Edit Blog Category')

@section('content')
    <form method="POST" action="{{ route('admin.blog-categories.update', $blogCategory) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="admin-card max-w-3xl">
            <h2 class="text-lg font-semibold">Category Details</h2>
            <div class="mt-4 grid gap-5">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $blogCategory->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Slug (optional)</label>
                    <input type="text" name="slug" value="{{ old('slug', $blogCategory->slug) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('description', $blogCategory->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update category</button>
            <a href="{{ route('admin.blog-categories.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

