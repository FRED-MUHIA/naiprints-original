@extends('admin.layout')

@section('title', 'Brands')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage the brands your products belong to.</p>
        <a href="{{ route('admin.brands.create') }}" class="admin-btn-primary">
            New brand
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
                @forelse ($brands as $brand)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $brand->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $brand->slug }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="text-slate-700 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this brand?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-slate-500">
                            No brands yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
