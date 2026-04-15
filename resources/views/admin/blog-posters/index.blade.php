@extends('admin.layout')

@section('title', 'Blog Posters')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage sidebar poster banners shown on blog detail pages.</p>
        <a href="{{ route('admin.blog-posters.create') }}" class="admin-btn-primary">
            New poster
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3">Poster</th>
                    <th class="px-6 py-3">Linked Blog</th>
                    <th class="px-6 py-3">Position</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($posters as $poster)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="product-image-thumb overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    @if ($poster->image_url)
                                        <img src="{{ $poster->image_url }}" alt="{{ $poster->alt_text ?: ($poster->title ?: 'Poster') }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-400">
                                            P
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $poster->title ?: 'Untitled poster' }}</p>
                                    <p class="text-xs text-slate-500">{{ $poster->link_url ?: 'No link URL' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $poster->blog?->title ?? 'Global (all blogs)' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $poster->position }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $poster->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $poster->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.blog-posters.edit', $poster) }}" class="text-slate-700 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.blog-posters.destroy', $poster) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this poster?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-slate-500">
                            No blog posters yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

