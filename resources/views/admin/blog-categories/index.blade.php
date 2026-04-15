@extends('admin.layout')

@section('title', 'Blog Categories')

@section('content')
    @if (($blogTablesReady ?? true) === false)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Blog tables are not available yet. Run <code>php artisan migrate --force</code> on this server.
        </div>
    @endif

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Group blog posts into meaningful categories.</p>
        <a href="{{ route('admin.blog-categories.create') }}" class="admin-btn-primary">
            New category
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3">Posts</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                            <p class="text-xs text-slate-500">{{ $category->description ?: '—' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $category->slug }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ (int) $category->blogs_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.blog-categories.edit', $category) }}" class="text-slate-700 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this blog category?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-slate-500">No blog categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
