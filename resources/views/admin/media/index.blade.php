@extends('admin.layout')

@section('title', 'Media Library')

@section('content')
    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Media</p>
                <h2 class="mt-2 text-2xl font-semibold">Upload & manage media</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 lg:grid-cols-[2fr_1fr]">
            @csrf
            <div class="grid gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Media file</label>
                    <input type="file" name="file" accept="image/*,video/mp4" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                    <p class="mt-2 text-xs text-slate-500">Upload JPG, PNG, WEBP, or MP4 (max 20MB).</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Title</label>
                        <input type="text" name="title" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Alt text</label>
                        <input type="text" name="alt_text" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="admin-btn-primary w-full">Upload</button>
            </div>
        </form>
    </div>

    <div class="mt-6 admin-panel p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">All media</h3>
            <span class="text-xs text-slate-500">{{ $media->total() }} items</span>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($media as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    @php
                        $mediaPath = parse_url($item->url, PHP_URL_PATH) ?? '';
                        $mediaExt = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                        $isVideo = $mediaExt === 'mp4';
                    @endphp
                    <div class="product-image-gallery overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        @if ($isVideo)
                            <video
                                class="h-full w-full object-cover"
                                src="{{ $item->url }}"
                                controls
                                muted
                                playsinline
                                preload="metadata"
                                aria-label="{{ $item->title ?? 'Media video' }}"
                            ></video>
                        @else
                            <img src="{{ $item->url }}" alt="{{ $item->alt_text ?? $item->title ?? 'Media image' }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Link</label>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="text" value="{{ $item->url }}" readonly class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-600">
                                <button type="button" class="admin-btn-outline copy-btn" data-copy="{{ $item->url }}">Copy</button>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.media.update', $item) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Title</label>
                                <input type="text" name="title" value="{{ $item->title }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Alt Text</label>
                                <input type="text" name="alt_text" value="{{ $item->alt_text }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Replace File</label>
                                <input type="file" name="file" accept="image/*,video/mp4" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="admin-btn-primary">Update</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn-outline" onclick="return confirm('Delete this media?')">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-empty sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    No media yet. Upload your first file.
                </div>
            @endforelse
        </div>
        <div class="mt-6">
            {{ $media->links() }}
        </div>
    </div>

    <script>
        document.querySelectorAll('.copy-btn').forEach((button) => {
            button.addEventListener('click', () => {
                const value = button.dataset.copy;
                if (!value) {
                    return;
                }
                navigator.clipboard.writeText(value);
                button.textContent = 'Copied';
                setTimeout(() => {
                    button.textContent = 'Copy';
                }, 1500);
            });
        });
    </script>
@endsection
