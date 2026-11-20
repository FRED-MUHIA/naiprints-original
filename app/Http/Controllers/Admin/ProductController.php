<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSelectionImage;
use App\Models\Size;
use App\Models\Tag;
use App\Support\ClothingRailCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'categories', 'brand'])->orderByDesc('created_at')->get();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $attributes = Attribute::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();

        return view('admin.products.create', compact('brands', 'categories', 'tags', 'attributes', 'sizes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request);
        $data = $validator->validate();
        $data['color_options'] = $this->parseColorOptions($request->input('color_options'));
        $categoryIds = $this->resolveCategoryIds($request);
        $data['category_id'] = $categoryIds[0] ?? null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $data['slug'] = $this->makeUniqueSlug($data['name']);
        $data['discount_value'] = $data['discount_type'] === 'none' ? null : $data['discount_value'];
        $data['stock'] = $data['stock'] ?? 0;

        $product = Product::create($data);
        $this->syncCategories($product, $categoryIds);

        $this->syncAlternateImages($product, $request);
        $this->syncGallery($product, $request);
        $this->syncRailImages($product, $request);
        if ($this->hasMaasaiCategory($categoryIds)) {
            $this->syncSelectionImages($product, $request);
        }
        $this->syncRelations($product, $request);
        ClothingRailCache::bust();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['tags', 'attributes', 'sizes', 'alternateImages', 'galleryImages', 'railImages', 'categories']);
        if (Schema::hasTable('product_selection_images')) {
            $product->load('selectionImages');
        }
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $attributes = Attribute::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'tags', 'attributes', 'sizes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = $this->validator($request);
        $data = $validator->validate();
        $data['color_options'] = $this->parseColorOptions($request->input('color_options'));
        $categoryIds = $this->resolveCategoryIds($request, $product);
        $data['category_id'] = $categoryIds[0] ?? null;

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $data['slug'] = $this->makeUniqueSlug($data['name'], $product->id);
        $data['discount_value'] = $data['discount_type'] === 'none' ? null : $data['discount_value'];
        $data['stock'] = $data['stock'] ?? 0;

        $product->update($data);
        $this->syncCategories($product, $categoryIds);

        $this->syncAlternateImages($product, $request);
        $this->syncGallery($product, $request);
        $this->syncRailImages($product, $request);
        if ($this->hasMaasaiCategory($categoryIds)) {
            $this->syncSelectionImages($product, $request);
        }

        if ($request->boolean('sync_relations', true)) {
            $this->syncRelations($product, $request);
        }
        ClothingRailCache::bust();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        if (Schema::hasTable('product_selection_images')) {
            foreach ($product->selectionImages as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
        }
        $product->delete();
        ClothingRailCache::bust();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    private function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:255'],
            'color_options' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'rail_hanger_style' => ['nullable', 'in:hook_only,full'],
            'rail_image_overlap' => ['nullable', 'integer', 'min:0', 'max:24'],
            'rail_side_image' => ['nullable', 'image', 'max:8192'],
            'rail_back_image' => ['nullable', 'image', 'max:8192'],
            'alternate_images' => ['array'],
            'alternate_images.*' => ['image', 'max:2048'],
            'remove_alternates' => ['array'],
            'remove_alternates.*' => ['integer', 'exists:product_images,id'],
            'gallery_images' => ['array'],
            'gallery_images.*' => ['image', 'max:2048'],
            'remove_gallery' => ['array'],
            'remove_gallery.*' => ['integer', 'exists:product_images,id'],
            'selection_images' => ['array'],
            'selection_images.*.label' => ['nullable', 'string', 'max:120'],
            'selection_images.*.position' => ['nullable', 'integer', 'min:0'],
            'selection_images.*.remove' => ['nullable'],
            'new_selection_images' => ['array'],
            'new_selection_images.*.label' => ['nullable', 'string', 'max:120'],
            'new_selection_images.*.image' => ['nullable', 'image', 'max:8192'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:none,percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active'],
            'tags' => ['array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('discount_type') !== 'none' && $request->input('discount_value') === null) {
                $validator->errors()->add('discount_value', 'Discount value is required.');
            }

            if ($request->input('discount_type') === 'percent' && $request->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'Percent discount cannot exceed 100.');
            }
        });

        return $validator;
    }

    private function resolveCategoryIds(Request $request, ?Product $product = null): array
    {
        $primaryCategoryId = $request->input('category_id');
        $primaryCategoryId = is_numeric($primaryCategoryId) ? (int) $primaryCategoryId : null;

        if ($request->exists('category_ids')) {
            $selected = $request->input('category_ids', []);
            if (!is_array($selected)) {
                $selected = [];
            }

            $categoryIds = collect($selected)
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($primaryCategoryId) {
                $categoryIds->prepend($primaryCategoryId);
            }

            return $categoryIds->unique()->values()->all();
        }

        if ($product && !$request->boolean('sync_relations', true)) {
            $existingCategoryIds = $product->categories()
                ->pluck('categories.id')
                ->map(fn ($id) => (int) $id);

            if ($primaryCategoryId) {
                $existingCategoryIds->prepend($primaryCategoryId);
            }

            return $existingCategoryIds->unique()->values()->all();
        }

        return $primaryCategoryId ? [$primaryCategoryId] : [];
    }

    private function syncCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    private function hasMaasaiCategory(array $categoryIds): bool
    {
        if (empty($categoryIds)) {
            return false;
        }

        return Category::query()
            ->whereIn('id', $categoryIds)
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(slug) LIKE ?', ['%maasai%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%maasai%']);
            })
            ->exists();
    }

    private function parseColorOptions(?string $raw): ?array
    {
        if ($raw === null) {
            return null;
        }

        $colors = preg_split('/[,\n]+/', $raw);
        if (!$colors) {
            return null;
        }

        $colors = array_map('trim', $colors);
        $colors = array_filter($colors, fn ($color) => $color !== '');
        $colors = array_map(function ($color) {
            $color = preg_replace('/[^#a-zA-Z0-9(),.%\\s-]/', '', $color);
            return trim($color);
        }, $colors);

        $colors = array_values(array_unique(array_filter($colors)));

        return $colors ?: null;
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function syncRelations(Product $product, Request $request): void
    {
        $product->tags()->sync($request->input('tags', []));

        $attributeSync = [];
        foreach ($request->input('attribute_values', []) as $attributeId => $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $attributeSync[$attributeId] = ['value' => $value];
            }
        }
        $product->attributes()->sync($attributeSync);

        $sizeSync = [];
        foreach ($request->input('sizes', []) as $sizeId => $payload) {
            if (!isset($payload['enabled'])) {
                continue;
            }

            $price = $payload['price'] ?? null;
            $stock = $payload['stock'] ?? null;

            $sizeSync[$sizeId] = [
                'price' => is_numeric($price) ? $price : null,
                'stock' => is_numeric($stock) ? (int) $stock : null,
            ];
        }
        $product->sizes()->sync($sizeSync);
    }

    private function syncGallery(Product $product, Request $request): void
    {
        $removeIds = $request->input('remove_gallery', []);
        if (!empty($removeIds)) {
            $images = ProductImage::where('product_id', $product->id)
                ->where('type', 'gallery')
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        if ($request->hasFile('gallery_images')) {
            $nextPosition = (int) $product->images()->where('type', 'gallery')->max('position');
            foreach ($request->file('gallery_images') as $file) {
                $nextPosition++;
                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'position' => $nextPosition,
                    'type' => 'gallery',
                ]);
            }
        }
    }

    private function syncRailImages(Product $product, Request $request): void
    {
        foreach (['side', 'back'] as $view) {
            $field = 'rail_' . $view . '_image';
            if (!$request->hasFile($field)) {
                continue;
            }

            $existing = ProductImage::query()
                ->where('product_id', $product->id)
                ->where('view', $view)
                ->first();

            if ($existing) {
                Storage::disk('public')->delete($existing->image_path);
                $existing->delete();
            }

            $path = $request->file($field)->store('products/rail', 'public');
            $product->images()->create([
                'image_path' => $path,
                'position' => $view === 'side' ? 0 : 1,
                'type' => 'gallery',
                'view' => $view,
            ]);
        }
    }

    private function syncAlternateImages(Product $product, Request $request): void
    {
        $removeIds = $request->input('remove_alternates', []);
        if (!empty($removeIds)) {
            $images = ProductImage::where('product_id', $product->id)
                ->where('type', 'alternate')
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        if ($request->hasFile('alternate_images')) {
            $nextPosition = (int) $product->images()->where('type', 'alternate')->max('position');
            foreach ($request->file('alternate_images') as $file) {
                $nextPosition++;
                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'position' => $nextPosition,
                    'type' => 'alternate',
                ]);
            }
        }
    }

    private function syncSelectionImages(Product $product, Request $request): void
    {
        if (!Schema::hasTable('product_selection_images')) {
            return;
        }

        foreach ($request->input('selection_images', []) as $imageId => $payload) {
            $image = ProductSelectionImage::where('product_id', $product->id)
                ->whereKey($imageId)
                ->first();

            if (!$image) {
                continue;
            }

            if (!empty($payload['remove'])) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
                continue;
            }

            $label = trim((string) ($payload['label'] ?? ''));
            if ($label !== '') {
                $image->label = $label;
            }

            if (isset($payload['position']) && is_numeric($payload['position'])) {
                $image->position = max(0, (int) $payload['position']);
            }

            $image->save();
        }

        $newRows = $request->input('new_selection_images', []);
        $newFiles = $request->file('new_selection_images', []);
        $nextPosition = (int) $product->selectionImages()->max('position');

        foreach ($newFiles as $index => $payload) {
            $file = is_array($payload) ? ($payload['image'] ?? null) : null;
            if (!$file) {
                continue;
            }

            $label = trim((string) ($newRows[$index]['label'] ?? ''));
            $nextPosition++;

            $product->selectionImages()->create([
                'label' => $label !== '' ? $label : 'Option ' . $nextPosition,
                'image_path' => $file->store('products/selections', 'public'),
                'position' => $nextPosition,
            ]);
        }
    }
}
