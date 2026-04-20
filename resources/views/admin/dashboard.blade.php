@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    @if (isset($isTaskModuleReady) && !$isTaskModuleReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Task module requires migration on this server. Run <code>php artisan migrate</code> in the project directory.
        </div>
    @endif

    <div class="admin-card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Task management</p>
                <h2 class="mt-2 text-2xl font-semibold">Production Tasks</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.tasks.index') }}" class="admin-btn-outline">Open Task Board</a>
                <a href="{{ route('admin.tasks.create') }}" class="admin-btn-primary">Assign New Task</a>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total</p>
                <p class="mt-2 text-2xl font-semibold">{{ $taskStats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Assigned</p>
                <p class="mt-2 text-2xl font-semibold">{{ $taskStats['assigned'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">In Progress</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">{{ $taskStats['in_progress'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Finished</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $taskStats['finished'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Delivered</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $taskStats['delivered'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Client</th>
                        <th>Assigned Time</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($recentTasks ?? collect()) as $task)
                        @php
                            $statusClass = match ($task->status) {
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'finished' => 'bg-teal-100 text-teal-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $task->reference }} - {{ $task->title }}</td>
                            <td>{{ $task->client?->display_name ?? '-' }}</td>
                            <td>{{ optional($task->assigned_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                Stage {{ $task->current_stage }}/{{ $task->total_stages }}
                                <span class="text-xs text-slate-500">({{ $task->progress_percent }}%)</span>
                            </td>
                            <td>
                                <span class="admin-status {{ $statusClass }}">{{ $task->status_label }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="admin-btn-outline">Update</a>
                                    <a href="{{ route('admin.tasks.work-note', $task) }}" class="admin-btn-outline">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">No tasks assigned yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)]">
        <div class="admin-panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product management</p>
                    <h2 class="mt-2 text-3xl font-semibold">Products</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="admin-search">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.03 4.11l3.43 3.43a.75.75 0 1 1-1.06 1.06l-3.43-3.43A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                        <input type="text" placeholder="Search product..." class="admin-input">
                    </div>
                    <a href="{{ route('admin.categories.create') }}" class="admin-btn-outline">
                        Add Category
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">
                        Add New Product
                    </a>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="admin-pill {{ request('category') ? '' : 'admin-pill-active' }}">
                    All products
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('admin.dashboard', ['category' => $category->id]) }}"
                       class="admin-pill {{ (string) request('category') === (string) $category->id ? 'admin-pill-active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('admin.categories.create') }}" class="admin-pill">
                    Add Category
                </a>
                <div class="ml-auto">
                    <button class="admin-pill">Sort by</button>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    @php
                        $categoryNames = $product->categories->pluck('name')->filter()->values();
                        if ($categoryNames->isEmpty() && $product->category?->name) {
                            $categoryNames = collect([$product->category->name]);
                        }
                    @endphp
                    <div class="admin-product-card">
                        <div class="admin-product-media">
                            <div class="admin-product-image">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full rounded-[18px] object-cover">
                                @else
                                    <span class="admin-product-tag">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $categoryNames->isNotEmpty() ? $categoryNames->join(', ') : 'Uncategorized' }}</p>
                            <p class="mt-3 text-sm font-semibold text-slate-900">KSh {{ number_format($product->price, 2) }}</p>
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                <span>Stock {{ $product->stock }}</span>
                                <span class="admin-status {{ $product->status === 'active' ? 'admin-status-active' : 'admin-status-draft' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="mt-3 inline-flex text-xs font-semibold text-brand underline underline-offset-4">
                                Edit product
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty sm:col-span-2 xl:col-span-3">
                        No products yet. Add your first product to populate the grid.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm text-slate-500">
                <span>Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</span>
                <div class="rounded-full border border-slate-200 bg-white px-4 py-2">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <div class="admin-panel p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Edit products</h3>
                    <p class="text-xs text-slate-500">Update the highlighted product.</p>
                </div>
                @if ($featuredProduct)
                    <a href="{{ route('admin.products.edit', $featuredProduct) }}" class="text-xs font-semibold text-brand underline underline-offset-4">
                        See full view
                    </a>
                @endif
            </div>

            <div class="mt-4 flex gap-2">
                <button class="admin-tab admin-tab-active">Descriptions</button>
                <button class="admin-tab">Inventory</button>
                <button class="admin-tab">Pricing</button>
            </div>

            @if ($featuredProduct)
                <div class="mt-6 admin-product-media">
                    <div class="admin-product-image admin-product-image-lg">
                        @if ($featuredProduct->image_url)
                            <img src="{{ $featuredProduct->image_url }}" alt="{{ $featuredProduct->name }}" class="h-full w-full rounded-[18px] object-cover">
                        @else
                            <span class="admin-product-tag">{{ strtoupper(substr($featuredProduct->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.products.update', $featuredProduct) }}" class="mt-6 space-y-5" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="sync_relations" value="0">
                    <input type="hidden" name="discount_type" value="{{ $featuredProduct->discount_type }}">
                    <input type="hidden" name="discount_value" value="{{ $featuredProduct->discount_value }}">

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product name</label>
                        <input type="text" name="name" value="{{ old('name', $featuredProduct->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Description</label>
                        <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">{{ old('description', $featuredProduct->description) }}</textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Category</label>
                            <select name="category_id" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $featuredProduct->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Brand</label>
                            <select name="brand_id" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">No brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $featuredProduct->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Price (KSh)</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $featuredProduct->price) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Stock</label>
                            <input type="number" min="0" name="stock" value="{{ old('stock', $featuredProduct->stock) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product image</label>
                        <input type="file" name="image" accept="image/*" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Status</label>
                            <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="draft" @selected(old('status', $featuredProduct->status) === 'draft')>Draft</option>
                                <option value="active" @selected(old('status', $featuredProduct->status) === 'active')>Active</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $featuredProduct->sku) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.products.edit', $featuredProduct) }}" class="admin-btn-outline">
                            Discard
                        </a>
                        <button type="submit" class="admin-btn-primary">
                            Update product
                        </button>
                    </div>
                </form>
            @else
                <div class="admin-empty mt-6">
                    No products to edit yet. Add a product to get started.
                </div>
            @endif
        </div>
    </div>
@endsection
