@extends('admin.layout')

@section('title', 'Edit Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="admin-card">
            <h2 class="text-lg font-semibold">Edit Category</h2>
            <div class="mt-4 grid gap-5">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                    <p class="mt-2 text-xs text-slate-500">The name is how it appears on your site.</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    <p class="mt-2 text-xs text-slate-500">The “slug” is the URL-friendly version of the name.</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Parent category</label>
                    <select name="parent_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <option value="">None</option>
                        @foreach ($categories as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Assign a parent term to create a hierarchy.</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Meta description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('description', $category->description) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">The description is not prominent by default.</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Display type</label>
                    <select name="display_type" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <option value="default" @selected(old('display_type', $category->display_type) === 'default')>Default</option>
                        <option value="featured" @selected(old('display_type', $category->display_type) === 'featured')>Featured</option>
                        <option value="collection" @selected(old('display_type', $category->display_type) === 'collection')>Collection</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    @if ($category->thumbnail_url)
                        <div class="product-image-gallery mt-3 overflow-hidden rounded-2xl border border-slate-200">
                            <img src="{{ $category->thumbnail_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update category</button>
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
