@extends('admin.layout')

@section('title', 'Sizes')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage garment sizing options.</p>
        <a href="{{ route('admin.sizes.create') }}" class="admin-btn-primary">
            New size
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sizes as $size)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $size->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $size->slug }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.sizes.edit', $size) }}" class="text-slate-700 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.sizes.destroy', $size) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this size?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-slate-500">
                            No sizes yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
