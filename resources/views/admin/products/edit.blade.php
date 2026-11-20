@extends('admin.layout')

@section('title', 'Edit Product')

@section('content')
    @php
        $selectedTags = $product->tags->pluck('id')->all();
        $attributeValues = $product->attributes->pluck('pivot.value', 'id')->toArray();
        $sizeMap = $product->sizes->keyBy('id');
        $storedColors = $product->color_options ?? [];

        $oldTags = old('tags', $selectedTags);
        $oldAttributes = old('attribute_values', $attributeValues);
        $oldSizes = old('sizes', []);
        $oldColors = old('color_options', $storedColors);
        $colorValue = is_array($oldColors) ? implode(', ', $oldColors) : $oldColors;
        $primaryCategoryId = old('category_id', $product->category_id);
        $selectedCategoryIds = collect(old('category_ids', $product->categories->pluck('id')->all()))
            ->when($primaryCategoryId, fn ($ids) => $ids->push($primaryCategoryId))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $alternateImages = $product->alternateImages->values();
        $galleryImages = $product->galleryImages->values();
        $railImages = $product->railImages->keyBy('view');
    @endphp

    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-8" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="sync_relations" value="1">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div id="product-info" class="admin-card">
                    <h2 class="text-lg font-semibold">Product info</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Name</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Primary category</label>
                            <select name="category_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" data-primary-category-select>
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $category)
                                    @php
                                        $isMaasaiCategory = str_contains(strtolower((string) ($category->slug ?? $category->name)), 'maasai')
                                            || str_contains(strtolower((string) $category->name), 'maasai');
                                    @endphp
                                    <option value="{{ $category->id }}" data-is-maasai="{{ $isMaasaiCategory ? '1' : '0' }}" @selected((string) $primaryCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Used for legacy display and breadcrumb fallbacks.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Categories (multiple)</label>
                            <select name="category_ids[]" multiple size="6" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" data-product-category-select>
                                @foreach ($categories as $category)
                                    @php
                                        $isMaasaiCategory = str_contains(strtolower((string) ($category->slug ?? $category->name)), 'maasai')
                                            || str_contains(strtolower((string) $category->name), 'maasai');
                                    @endphp
                                    <option value="{{ $category->id }}" data-is-maasai="{{ $isMaasaiCategory ? '1' : '0' }}" @selected(in_array((int) $category->id, $selectedCategoryIds, true))>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Hold Ctrl (Windows) or Cmd (Mac) to select more than one.</p>
                        </div>
                        @include('admin.products._selection-images', ['product' => $product])
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Brand</label>
                            <select name="brand_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="">No brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Color swatches</label>
                            <input type="text" name="color_options" value="{{ $colorValue }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" placeholder="e.g. Black, #ffffff, Navy">
                            <p class="mt-2 text-xs text-slate-500">Enter comma-separated color names or hex codes for swatches.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Front view / product image</label>
                            <input type="file" name="image" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">This is the main product photo and the front view shown when a garment is selected (max 2MB).</p>
                            @if ($product->image_url)
                                <div class="product-image-gallery mt-3 overflow-hidden rounded-2xl border border-slate-200">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Side view for clothing rail</label>
                            <input type="file" name="rail_side_image" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">Shown while the garment hangs in the rail. Optional; the front view is used if omitted.</p>
                            @if ($railImages->has('side'))
                                <div class="product-image-gallery mt-3 overflow-hidden rounded-2xl border border-slate-200">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($railImages['side']->image_path) }}" alt="{{ $product->name }} side view" class="h-full w-full object-contain">
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Back view for clothing rail</label>
                            <input type="file" name="rail_back_image" accept="image/*" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">The selected garment turns to this image after the front view. Optional.</p>
                            @if ($railImages->has('back'))
                                <div class="product-image-gallery mt-3 overflow-hidden rounded-2xl border border-slate-200">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($railImages['back']->image_path) }}" alt="{{ $product->name }} back view" class="h-full w-full object-contain">
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Side-view hanger style</label>
                            <select name="rail_hanger_style" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="hook_only" @selected(old('rail_hanger_style', $product->rail_hanger_style ?? 'hook_only') === 'hook_only')>Center hook only</option>
                                <option value="full" @selected(old('rail_hanger_style', $product->rail_hanger_style) === 'full')>Full hanger</option>
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Choose whether the resting side-view image shows only the center hook or the full hanger outline.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Image overlap with hanger (px)</label>
                            <input type="number" name="rail_image_overlap" min="0" max="24" step="1" value="{{ old('rail_image_overlap', $product->rail_image_overlap ?? 12) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            <p class="mt-2 text-xs text-slate-500">Adjust how far the garment image rises behind the hanger. Use 0 for no overlap.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Alternative images (hover)</label>
                            <input type="file" name="alternate_images[]" accept="image/*" multiple class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            @if ($alternateImages->count() > 0)
                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($alternateImages as $image)
                                        <label class="flex flex-col gap-2 rounded-2xl border border-slate-200 p-3 text-xs text-slate-500">
                                            <div class="product-image-gallery overflow-hidden rounded-xl">
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="Alternate image" class="h-full w-full object-cover">
                                            </div>
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" name="remove_alternates[]" value="{{ $image->id }}" class="h-4 w-4 rounded border-slate-300">
                                                Remove
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div id="product-description">
                            <label class="text-sm font-semibold text-slate-700">Description</label>
                            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white" data-product-description-editor>
                                <div class="flex justify-end border-b border-slate-200 bg-slate-50 px-3 py-3">
                                    <div class="inline-flex overflow-hidden rounded-xl border border-slate-300">
                                        <button type="button" data-editor-mode="visual" class="bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Visual
                                        </button>
                                        <button type="button" data-editor-mode="code" class="border-l border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            Code
                                        </button>
                                    </div>
                                </div>
                                <textarea data-product-description-textarea name="description" rows="12" class="w-full px-4 py-3 text-sm leading-7 focus:outline-none">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Use headings, paragraphs, and bullet lists for the storefront product description.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Gallery</h2>
                            <p class="mt-1 text-sm text-slate-500">Upload multiple images for this product.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Add gallery images</label>
                            <input type="file" name="gallery_images[]" accept="image/*" multiple class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        @if ($galleryImages->count() > 0)
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($galleryImages as $image)
                                    <label class="flex flex-col gap-2 rounded-2xl border border-slate-200 p-3 text-xs text-slate-500">
                                        <div class="product-image-gallery overflow-hidden rounded-xl">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="Gallery image" class="h-full w-full object-cover">
                                        </div>
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="remove_gallery[]" value="{{ $image->id }}" class="h-4 w-4 rounded border-slate-300">
                                            Remove
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-500">No gallery images yet.</p>
                        @endif
                    </div>
                </div>

                <div id="pricing" class="admin-card">
                    <h2 class="text-lg font-semibold">Pricing & inventory</h2>
                    <div class="mt-4 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Base price (KSh)</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none" required>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Stock (units)</label>
                            <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Discount type</label>
                            <select name="discount_type" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="none" @selected(old('discount_type', $product->discount_type) === 'none')>None</option>
                                <option value="percent" @selected(old('discount_type', $product->discount_type) === 'percent')>Percent</option>
                                <option value="amount" @selected(old('discount_type', $product->discount_type) === 'amount')>Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Discount value</label>
                            <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $product->discount_value) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                                <option value="draft" @selected(old('status', $product->status) === 'draft')>Draft</option>
                                <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="meta-data" class="admin-card">
                    <h2 class="text-lg font-semibold">Meta data</h2>
                    <div class="mt-4 grid gap-5">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Meta description</label>
                            <input type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description) }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                </div>

                <div id="attributes" class="admin-card">
                    <h2 class="text-lg font-semibold">Attributes</h2>
                    <p class="mt-2 text-sm text-slate-500">Add custom values for each attribute.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($attributes as $attribute)
                            <div>
                                <label class="text-sm font-semibold text-slate-700">{{ $attribute->name }}</label>
                                <input type="text" name="attribute_values[{{ $attribute->id }}]" value="{{ $oldAttributes[$attribute->id] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Add attributes to start using them.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Tags</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($tags as $tag)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $oldTags)) class="h-4 w-4 rounded border-slate-300">
                                {{ $tag->name }}
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">No tags created yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="text-lg font-semibold">Sizes & inventory</h2>
                    <p class="mt-2 text-sm text-slate-500">Enable sizes and set optional price or stock overrides.</p>
                    <div class="mt-4 space-y-4">
                        @forelse ($sizes as $size)
                            @php
                                $existing = $sizeMap->get($size->id);
                                $sizeOld = $oldSizes[$size->id] ?? [
                                    'enabled' => $existing ? 'on' : null,
                                    'price' => $existing?->pivot?->price,
                                    'stock' => $existing?->pivot?->stock,
                                ];
                            @endphp
                            <div class="rounded-xl border border-slate-200 p-4">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="sizes[{{ $size->id }}][enabled]" @checked(isset($sizeOld['enabled'])) class="h-4 w-4 rounded border-slate-300">
                                    {{ $size->name }}
                                </label>
                                <div class="mt-3 grid gap-3 text-sm">
                                    <div>
                                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Price override</label>
                                        <input type="number" step="0.01" min="0" name="sizes[{{ $size->id }}][price]" value="{{ $sizeOld['price'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Stock override</label>
                                        <input type="number" min="0" name="sizes[{{ $size->id }}][stock]" value="{{ $sizeOld['stock'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No sizes created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="admin-btn-primary">Update product</button>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-outline">Cancel</a>
        </div>
    </form>
@endsection

@include('admin.products._description-editor')
