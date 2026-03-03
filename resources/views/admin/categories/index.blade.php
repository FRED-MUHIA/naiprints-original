@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage product categories for your store.</p>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">
            New category
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                <tr>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3">Parent</th>
                    <th class="px-6 py-3">Display</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="product-image-thumb overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    @if ($category->thumbnail_url)
                                        <img src="{{ $category->thumbnail_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-400">
                                            {{ strtoupper(substr($category->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $category->meta_description ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $category->slug }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $category->parent?->name ?? 'None' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ ucfirst($category->display_type ?? 'default') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-slate-700 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this category?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-slate-500">
                            No categories yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
