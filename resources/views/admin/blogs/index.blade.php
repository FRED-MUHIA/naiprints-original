@extends('admin.layout')

@section('title', 'Blogs')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage blog posts, SEO fields, and publication status.</p>
        <a href="{{ route('admin.blogs.create') }}" class="admin-btn-primary">
            New blog post
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3">Title</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Published</th>
                    <th class="px-6 py-3">Meta title</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($blogs as $blog)
                    <tr>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $blog->title }}</p>
                                @if ($blog->status === 'published')
                                    <a href="{{ route('blogs.show', $blog) }}" class="text-xs text-brand hover:underline">
                                        /blogs/{{ $blog->slug }}
                                    </a>
                                @else
                                    <p class="text-xs text-slate-500">/blogs/{{ $blog->slug }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $blog->category?->name ?? 'Uncategorized' }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $blog->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($blog->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $blog->published_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $blog->meta_title ?: '—' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-slate-700 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this blog post?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-slate-500">
                            No blog posts yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
