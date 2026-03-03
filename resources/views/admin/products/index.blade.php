@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage your product catalog and inventory.</p>
        <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">
            New product
        </a>
    </div>

    <div class="mt-6 overflow-hidden admin-panel">
        <table class="admin-table">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                <tr>
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Brand</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Discount</th>
                    <th class="px-6 py-3">Stock</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="product-image-thumb overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-400">
                                            {{ strtoupper(substr($product->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $product->sku ?? 'No SKU' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $product->brand?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-700">KSh {{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4 text-slate-500">
                            @if ($product->discount_type === 'none' || !$product->discount_value)
                                —
                            @elseif ($product->discount_type === 'percent')
                                {{ $product->discount_value }}%
                            @else
                                KSh {{ number_format($product->discount_value, 2) }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $product->stock }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-slate-700 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Delete this product?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-6 text-center text-slate-500">
                            No products yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
