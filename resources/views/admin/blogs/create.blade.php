@extends('admin.layout')

@section('title', 'Create Blog Post')

@section('content')
    <form method="POST" action="{{ route('admin.blogs.store') }}" class="space-y-8" enctype="multipart/form-data">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Post Details</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Slug (optional)</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-from-title" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Category</label>
                            <select name="blog_category_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('blog_category_id') === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Excerpt</label>
                            <textarea name="excerpt" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('excerpt') }}</textarea>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Content</label>
                            <textarea name="content" rows="14" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm leading-7 focus:border-brand focus:outline-none" required>{{ old('content') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Use line breaks to separate paragraphs for readable blog layout.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="text-lg font-semibold">SEO Meta</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta description</label>
                            <input type="text" name="meta_description" value="{{ old('meta_description') }}" maxlength="255" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Publish</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                <option value="published" @selected(old('status') === 'published')>Published</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Published at</label>
                            <input type="datetime-local"
                                   name="published_at"
                                   value="{{ old('published_at') }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">Leave blank to publish immediately when status is Published.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Cover Image</h2>
                    <div class="mt-4 space-y-3">
                        <input type="file" name="cover_image" accept="image/*" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <p class="text-xs text-slate-500">Recommended ratio: 16:10. Max size: 2MB.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Save blog post</button>
            <a href="{{ route('admin.blogs.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

