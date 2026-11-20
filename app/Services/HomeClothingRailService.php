<?php

namespace App\Services;

use App\Models\Product;
use App\Support\ClothingRailCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeClothingRailService
{
    private const APPAREL_TERMS = [
        'apparel', 'clothing', 'garment', 't-shirt', 'tshirt', 'tee', 'shirt',
        'hoodie', 'sweatshirt', 'sweater', 'polo', 'jersey', 'uniform', 'cap', 'hat',
    ];

    public function products(array $categoryIds = [], int $limit = 10): Collection
    {
        $categoryIds = collect($categoryIds)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();
        $limit = max(1, min(24, $limit));
        $cacheKey = 'home.clothing-rail.products.v4.' . ClothingRailCache::version() . '.' . sha1(json_encode([$categoryIds, $limit]));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($categoryIds, $limit) {
            $baseQuery = fn () => Product::query()
                ->with(['category', 'categories', 'brand', 'sizes', 'galleryImages', 'alternateImages', 'images', 'railImages'])
                ->where('status', 'active')
                ->where('stock', '>', 0)
                ->where(function ($imageQuery) {
                    $imageQuery
                        ->where(function ($pathQuery) {
                            $pathQuery->whereNotNull('image_path')->where('image_path', '<>', '');
                        })
                        ->orWhereHas('images', fn ($relatedImages) => $relatedImages->whereNotNull('image_path')->where('image_path', '<>', ''));
                });

            if ($categoryIds !== []) {
                $products = $baseQuery()
                    ->where(function ($query) use ($categoryIds) {
                        $query->whereIn('category_id', $categoryIds)
                            ->orWhereHas('categories', fn ($categories) => $categories->whereIn('categories.id', $categoryIds));
                    })
                    ->orderByDesc('stock')
                    ->orderByDesc('created_at')
                    ->take($limit * 3)
                    ->get()
                    ->map(fn (Product $product) => $this->present($product))
                    ->filter(fn (array $product) => !empty($product['image']))
                    ->take($limit)
                    ->values();
            } else {
                $apparelProducts = $baseQuery()
                    ->where(function ($categoryQuery) {
                        $categoryQuery->whereHas('category', fn ($category) => $this->applyApparelTerms($category))
                            ->orWhereHas('categories', fn ($category) => $this->applyApparelTerms($category));
                    })
                    ->orderByDesc('stock')
                    ->orderByDesc('created_at')
                    ->take($limit * 2)
                    ->get();

                $railProducts = $apparelProducts
                    ->map(fn (Product $product) => $this->present($product))
                    ->filter(fn (array $product) => !empty($product['image']))
                    ->take($limit)
                    ->values();
                $remaining = $limit - $railProducts->count();
                if ($remaining > 0) {
                    $otherProducts = $baseQuery()
                        ->whereNotIn('id', $apparelProducts->pluck('id'))
                        ->orderByDesc('stock')
                        ->orderByDesc('created_at')
                        ->take($remaining * 3)
                        ->get()
                        ->map(fn (Product $product) => $this->present($product))
                        ->filter(fn (array $product) => !empty($product['image']))
                        ->take($remaining)
                        ->values();
                    $railProducts = $railProducts->concat($otherProducts)->take($limit)->values();
                }

                $products = $railProducts;
            }

            return $products;
        });
    }

    private function applyApparelTerms($query): void
    {
        $query->where(function ($termsQuery) {
            foreach (self::APPAREL_TERMS as $term) {
                $needle = '%' . str_replace(['%', '_'], ['\\%', '\\_'], strtolower($term)) . '%';
                $termsQuery->orWhereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(slug) LIKE ?', [$needle]);
            }
        });
    }

    private function present(Product $product): array
    {
        $image = $product->image_path && Storage::disk('public')->exists($product->image_path)
            ? $this->publicImagePath($product->image_path)
            : null;
        if (!$image) {
            $fallback = $product->galleryImages
                ->concat($product->alternateImages)
                ->concat($product->images)
                ->first(fn ($candidate) => $candidate->image_path && Storage::disk('public')->exists($candidate->image_path));
            if ($fallback) {
                $image = $this->publicImagePath($fallback->image_path);
            }
        }

        $regularPrice = (float) $product->price;
        $currentPrice = $regularPrice;
        $discountType = $product->discount_type ?: 'none';
        $discountValue = (float) ($product->discount_value ?? 0);
        if ($discountType === 'percent' && $discountValue > 0) {
            $currentPrice = max(0, $regularPrice * (1 - ($discountValue / 100)));
        } elseif ($discountType === 'amount' && $discountValue > 0) {
            $currentPrice = max(0, $regularPrice - $discountValue);
        }

        $category = $product->category?->name
            ?? $product->categories->first()?->name
            ?? 'Nai Prints collection';
        $sideImage = $product->railImages->firstWhere('view', 'side');
        $backImage = $product->railImages->firstWhere('view', 'back');
        $sidePath = $sideImage?->image_path && Storage::disk('public')->exists($sideImage->image_path)
            ? $this->publicImagePath($sideImage->image_path)
            : $image;
        $backPath = $backImage?->image_path && Storage::disk('public')->exists($backImage->image_path)
            ? $this->publicImagePath($backImage->image_path)
            : $image;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'url' => route('products.show', ['product' => ($product->slug ?: $product->id)]),
            'image' => $image,
            'front_image' => $image,
            'side_image' => $sidePath,
            'back_image' => $backPath,
            'has_back_image' => (bool) ($backImage?->image_path && Storage::disk('public')->exists($backImage->image_path)),
            'hanger_style' => $product->rail_hanger_style === 'full' ? 'full' : 'hook_only',
            'image_overlap' => max(0, min(24, (int) ($product->rail_image_overlap ?? 12))),
            'category' => $category,
            'price' => number_format($currentPrice, 0),
            'regular_price' => number_format($regularPrice, 0),
            'is_on_sale' => $currentPrice < $regularPrice,
            'stock' => (int) $product->stock,
            'sizes' => $product->sizes->pluck('name')->values()->all(),
            'colors' => $product->color_options ?? [],
            'alt' => $product->name . ' from Nai Prints',
        ];
    }

    private function publicImagePath(string $path): string
    {
        return '/storage/' . ltrim($path, '/');
    }
}
