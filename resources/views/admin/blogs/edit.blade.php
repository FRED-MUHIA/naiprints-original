@extends('admin.layout')

@section('title', 'Edit Blog Post')

@section('content')
    @php
        $publishedAtValue = old('published_at');
        if ($publishedAtValue === null) {
            $publishedAtValue = $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '';
        }
    @endphp

    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" class="space-y-8" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Post Details</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Title</label>
                            <input type="text" name="title" value="{{ old('title', $blog->title) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Slug (optional)</label>
                            <input type="text" name="slug" value="{{ old('slug', $blog->slug) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Category</label>
                            <select name="blog_category_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('blog_category_id', $blog->blog_category_id) === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Excerpt</label>
                            <textarea name="excerpt" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Content</label>
                            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white" data-blog-editor>
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-3 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" data-editor-action="add-media" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Media
                                        </button>
                                        <button type="button" data-editor-action="add-form" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Form
                                        </button>
                                        <button type="button" data-editor-action="add-contact-form" class="admin-btn-outline !rounded-xl !px-3 !py-1.5 !text-xs">
                                            Add Contact Form
                                        </button>
                                    </div>
                                    <div class="inline-flex overflow-hidden rounded-xl border border-slate-300">
                                        <button type="button" data-editor-mode="visual" class="bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Visual
                                        </button>
                                        <button type="button" data-editor-mode="code" class="border-l border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Code
                                        </button>
                                    </div>
                                </div>
                                <div data-blog-editor-toolbar class="hidden border-b border-slate-200 bg-white px-2 py-2">
                                    <span class="ql-formats">
                                        <select class="ql-header">
                                            <option value="1">Heading 1</option>
                                            <option value="2">Heading 2</option>
                                            <option value="3">Heading 3</option>
                                            <option selected>Paragraph</option>
                                        </select>
                                    </span>
                                    <span class="ql-formats">
                                        <button type="button" class="ql-bold"></button>
                                        <button type="button" class="ql-italic"></button>
                                        <button type="button" class="ql-underline"></button>
                                        <button type="button" class="ql-blockquote"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button type="button" class="ql-list" value="ordered"></button>
                                        <button type="button" class="ql-list" value="bullet"></button>
                                        <button type="button" class="ql-align" value=""></button>
                                        <button type="button" class="ql-align" value="center"></button>
                                        <button type="button" class="ql-align" value="right"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button type="button" class="ql-link"></button>
                                        <button type="button" class="ql-image"></button>
                                        <button type="button" class="ql-code-block"></button>
                                        <button type="button" class="ql-clean"></button>
                                    </span>
                                </div>
                                <div data-blog-editor-visual class="hidden"></div>
                                <textarea data-blog-editor-textarea name="content" rows="16" class="w-full px-4 py-3 text-sm leading-7 focus:outline-none" required>{{ old('content', $blog->content) }}</textarea>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Use rich formatting, links, media, and tables. Switch to Code for raw HTML editing.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 px-6 py-6">
                        <p class="text-2xl font-semibold uppercase tracking-[0.32em] text-slate-900">SEO READY</p>
                        <p class="mt-4 text-sm leading-relaxed text-slate-700">
                            Each post supports custom meta title, meta description, category organization, and clean slug URLs for better search visibility.
                        </p>
                    </div>

                    <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-semibold">SEO Meta</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta description</label>
                            <input type="text" name="meta_description" value="{{ old('meta_description', $blog->meta_description) }}" maxlength="255" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
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
                                <option value="draft" @selected(old('status', $blog->status) === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $blog->status) === 'published')>Published</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Published at</label>
                            <input type="datetime-local"
                                   name="published_at"
                                   value="{{ $publishedAtValue }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Cover Image</h2>
                    <div class="mt-4 space-y-3">
                        @if ($blog->cover_image_url)
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <img src="{{ $blog->cover_image_url }}" alt="{{ $blog->title }}" class="h-full w-full object-cover">
                            </div>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="remove_cover_image" value="1" class="h-4 w-4 rounded border-slate-300">
                                Remove current image
                            </label>
                        @endif
                        <input type="file" name="cover_image" accept="image/*" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <p class="text-xs text-slate-500">Upload a new image to replace the existing one.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update blog post</button>
            <a href="{{ route('admin.blogs.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

@include('admin.blogs._rich-editor')
