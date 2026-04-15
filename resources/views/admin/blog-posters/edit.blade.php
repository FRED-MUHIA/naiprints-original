@extends('admin.layout')

@section('title', 'Edit Blog Poster')

@section('content')
    <form method="POST" action="{{ route('admin.blog-posters.update', $blogPoster) }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="admin-card max-w-3xl">
            <h2 class="text-lg font-semibold">Poster Details</h2>
            <div class="mt-4 grid gap-5">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $blogPoster->title) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Blog (optional)</label>
                    <select name="blog_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <option value="">Global (show on all blogs)</option>
                        @foreach ($blogs as $blog)
                            <option value="{{ $blog->id }}" @selected((string) old('blog_id', $blogPoster->blog_id) === (string) $blog->id)>
                                {{ $blog->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Current image</label>
                    @if ($blogPoster->image_url)
                        <div class="mt-2 overflow-hidden rounded-xl border border-slate-200">
                            <img src="{{ $blogPoster->image_url }}" alt="{{ $blogPoster->alt_text ?: ($blogPoster->title ?: 'Poster') }}" class="h-full w-full object-cover">
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">No image available.</p>
                    @endif
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Replace image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Alt text</label>
                    <input type="text" name="alt_text" value="{{ old('alt_text', $blogPoster->alt_text) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Link URL (optional)</label>
                    <input type="url" name="link_url" value="{{ old('link_url', $blogPoster->link_url) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Position</label>
                    <input type="number" name="position" min="0" value="{{ old('position', $blogPoster->position) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $blogPoster->is_active)) class="h-4 w-4 rounded border-slate-300">
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $blogPoster->open_in_new_tab)) class="h-4 w-4 rounded border-slate-300">
                        Open link in new tab
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update poster</button>
            <a href="{{ route('admin.blog-posters.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

