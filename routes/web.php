<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\BlogPosterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\MpesaController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\MockupAiController;
use App\Http\Controllers\MockupCheckoutController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\PublicMpesaController;
use App\Http\Controllers\PublicReceiptController;
use App\Models\Attribute;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FinancialEntry;
use App\Models\GlobalSection;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Size;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Page;
use App\Models\User;
use App\Support\PageDefaults;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\CorporateQuoteController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\JerseyCatalogController;
use App\Http\Controllers\JerseyDesignController;
use App\Http\Controllers\JerseyLogoController;
use App\Http\Controllers\JerseyOrderController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Affiliate\AuthController as AffiliateAuthController;
use App\Http\Controllers\Affiliate\DashboardController as AffiliateDashboardController;

if (!function_exists('resolvePurchasePopupProducts')) {
    function resolvePurchasePopupProducts(array $popupContent): array
    {
        $items = $popupContent['items'] ?? [];
        $productIds = collect($items)
            ->map(fn ($item) => is_array($item) ? ($item['product_id'] ?? null) : null)
            ->filter()
            ->unique()
            ->values();
        $productNames = collect($items)
            ->map(function ($item) {
                if (!is_array($item)) {
                    return null;
                }
                $name = trim((string) ($item['product'] ?? ''));
                return $name !== '' ? Str::of($name)->lower()->trim()->toString() : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty() && $productNames->isEmpty()) {
            return [];
        }

        $products = Product::with(['galleryImages', 'alternateImages', 'images'])
            ->when(!$productIds->isEmpty(), fn ($query) => $query->whereIn('id', $productIds))
            ->when(!$productNames->isEmpty(), function ($query) use ($productNames) {
                $query->orWhereIn('name', $productNames);
            })
            ->get();

        return $products->mapWithKeys(function (Product $product) {
            $image = $product->image_url;
            if (!$image) {
                $fallback = $product->galleryImages->first()
                    ?? $product->alternateImages->first()
                    ?? $product->images->first();
                if ($fallback && $fallback->image_path) {
                    $image = Storage::url($fallback->image_path);
                }
            }

            $payload = [
                'name' => $product->name,
                'image' => $image ?? '',
                'url' => route('products.show', ['product' => $product->slug ?: $product->getKey()]),
            ];

            $nameKey = Str::of($product->name)->lower()->trim()->toString();

            return [
                $product->id => $payload,
                "name:{$nameKey}" => $payload,
            ];
        })->all();
    }
}

Route::get('/favicon.ico', function () {
    return response()
        ->file(public_path('images/logo.svg'), ['Content-Type' => 'image/svg+xml'])
        ->header('Cache-Control', 'public, max-age=604800');
});

Route::get('/', function () {
    $page = Page::ensure('home', 'Home', PageDefaults::home());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $categories = [
        [
            'title' => 'Uncategorized',
            'items' => 3,
            'gradient' => 'linear-gradient(135deg, #f4d2c6, #f8e5d9)',
        ],
        [
            'title' => 'T-shirt',
            'items' => 16,
            'gradient' => 'linear-gradient(135deg, #e5e7eb, #ffffff)',
        ],
        [
            'title' => 'Hoodie',
            'items' => 9,
            'gradient' => 'linear-gradient(135deg, #fbcfe8, #fdf2f8)',
        ],
        [
            'title' => 'Polo T-shirt',
            'items' => 1,
            'gradient' => 'linear-gradient(135deg, #f1d4b2, #fde68a)',
        ],
        [
            'title' => 'Pillows',
            'items' => 2,
            'gradient' => 'linear-gradient(135deg, #a7f3d0, #d1fae5)',
        ],
        [
            'title' => 'Kids',
            'items' => 8,
            'gradient' => 'linear-gradient(135deg, #e0f2fe, #f0f9ff)',
        ],
        [
            'title' => 'Corporate',
            'items' => 6,
            'gradient' => 'linear-gradient(135deg, #dbeafe, #eff6ff)',
        ],
        [
            'title' => 'Gifts',
            'items' => 5,
            'gradient' => 'linear-gradient(135deg, #fde68a, #fef3c7)',
        ],
    ];

    $products = [
        [
            'title' => 'Pray Minimal Tee',
            'price' => 'KSh 1,450',
            'tag' => 'Best seller',
            'swatch' => 'from-[#0f172a] via-[#111827] to-[#f59e0b]',
        ],
        [
            'title' => 'Own Path Hoodie',
            'price' => 'KSh 2,950',
            'tag' => 'New drop',
            'swatch' => 'from-[#0b1026] via-[#1f2937] to-[#e11d2e]',
        ],
        [
            'title' => 'Onyx Sports Set',
            'price' => 'KSh 3,200',
            'tag' => 'Limited',
            'swatch' => 'from-[#111827] via-[#0f172a] to-[#22c55e]',
        ],
        [
            'title' => 'Electric Pop Tee',
            'price' => 'KSh 1,750',
            'tag' => 'Vibrant',
            'swatch' => 'from-[#111827] via-[#312e81] to-[#f97316]',
        ],
        [
            'title' => 'Corporate Polo Pack',
            'price' => 'KSh 4,800',
            'tag' => 'Bulk',
            'swatch' => 'from-[#0f172a] via-[#1e293b] to-[#38bdf8]',
        ],
        [
            'title' => 'Kids Joy Set',
            'price' => 'KSh 1,300',
            'tag' => 'Soft touch',
            'swatch' => 'from-[#0f172a] via-[#334155] to-[#eab308]',
        ],
        [
            'title' => 'Couple Match Pack',
            'price' => 'KSh 2,600',
            'tag' => '2 pieces',
            'swatch' => 'from-[#111827] via-[#1f2937] to-[#fb7185]',
        ],
        [
            'title' => 'Birthday Crew Kit',
            'price' => 'KSh 3,400',
            'tag' => 'Event',
            'swatch' => 'from-[#0b1026] via-[#111827] to-[#a855f7]',
        ],
    ];

    $homeSections = $page->content['sections'] ?? [];
    $bestSellersSection = collect($homeSections)->first(function ($section) {
        return ($section['type'] ?? '') === 'best_sellers';
    });
    $featuredCategoryId = data_get($bestSellersSection, 'data.product_category_id');
    $featuredCategoryId = is_numeric($featuredCategoryId) ? (int) $featuredCategoryId : null;

    $saleFeatureSection = collect($homeSections)->first(function ($section) {
        return ($section['type'] ?? '') === 'sale_feature';
    });
    $saleFeatureCategoryId = data_get($saleFeatureSection, 'data.product_category_id');
    $saleFeatureCategoryId = is_numeric($saleFeatureCategoryId) ? (int) $saleFeatureCategoryId : null;
    $promoCategoryId = Category::query()
        ->whereRaw('LOWER(slug) = ?', ['promo'])
        ->orWhereRaw('LOWER(name) = ?', ['promo'])
        ->value('id');
    $promoCategoryId = is_numeric($promoCategoryId) ? (int) $promoCategoryId : null;

    $giftFinderSection = collect($homeSections)->first(function ($section) {
        return ($section['type'] ?? '') === 'gift_finder';
    });
    $giftFinderCategoryId = data_get($giftFinderSection, 'data.product_category_id');
    if (!is_numeric($giftFinderCategoryId)) {
        $howItWorksSection = collect($homeSections)->first(function ($section) {
            return ($section['type'] ?? '') === 'how_it_works';
        });
        $giftFinderCategoryId = data_get($howItWorksSection, 'data.gift_finder.product_category_id');
    }
    $giftFinderCategoryId = is_numeric($giftFinderCategoryId) ? (int) $giftFinderCategoryId : null;
    $giftFinderCategories = Category::orderBy('name')->get(['id', 'name', 'slug']);

    $homeProductRelations = ['category', 'categories', 'brand', 'sizes', 'tags', 'alternateImages', 'galleryImages'];
    $applyHomeCategoryFilter = static function ($query, ?int $categoryId): void {
        if ($categoryId) {
            $query->where(function ($categoryQuery) use ($categoryId) {
                $categoryQuery
                    ->where('category_id', $categoryId)
                    ->orWhereHas('categories', fn ($relatedCategoryQuery) => $relatedCategoryQuery->where('categories.id', $categoryId));
            });
            return;
        }

        $query->where(function ($categoryQuery) {
            $categoryQuery
                ->whereHas('category', function ($primaryCategoryQuery) {
                    $primaryCategoryQuery
                        ->whereRaw('LOWER(slug) = ?', ['design'])
                        ->orWhereRaw('LOWER(name) = ?', ['design']);
                })
                ->orWhereHas('categories', function ($relatedCategoryQuery) {
                    $relatedCategoryQuery
                        ->whereRaw('LOWER(slug) = ?', ['design'])
                        ->orWhereRaw('LOWER(name) = ?', ['design']);
                });
        });
    };

    $featuredProductsQuery = Product::with($homeProductRelations)->orderByDesc('created_at');
    $applyHomeCategoryFilter($featuredProductsQuery, $featuredCategoryId);
    $featuredProducts = $featuredProductsQuery->take(8)->get();

    $saleFeatureProductsQuery = Product::with($homeProductRelations)->orderByDesc('created_at');
    $applyHomeCategoryFilter($saleFeatureProductsQuery, $saleFeatureCategoryId ?: $promoCategoryId);
    $saleFeatureProducts = $saleFeatureProductsQuery->take(2)->get();
    if ($saleFeatureProducts->isEmpty() && ($saleFeatureCategoryId || $promoCategoryId)) {
        $saleFeatureProducts = Product::with($homeProductRelations)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();
    }

    $giftFinderProductsQuery = Product::with($homeProductRelations)->orderByDesc('created_at');
    $applyHomeCategoryFilter($giftFinderProductsQuery, $giftFinderCategoryId);
    $giftFinderProducts = $giftFinderProductsQuery->take(8)->get();
    if ($giftFinderProducts->isEmpty() && $giftFinderCategoryId) {
        $giftFinderProducts = Product::with($homeProductRelations)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();
    }

    return view('home', [
        'categories' => $categories,
        'giftFinderCategories' => $giftFinderCategories,
        'products' => $products,
        'featuredProducts' => $featuredProducts,
        'saleFeatureProducts' => $saleFeatureProducts,
        'giftFinderProducts' => $giftFinderProducts,
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
});

Route::get('/embroidery', function () {
    $page = Page::ensure('embroidery', 'Embroidery', PageDefaults::embroidery());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('embroidery', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('embroidery');

Route::get('/maasai', function () {
    $page = Page::ensure('maasai', 'Maasai', PageDefaults::maasai());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    $maasaiSections = $page->content['sections'] ?? [];
    $maasaiSection = collect($maasaiSections)->first(fn ($section) => ($section['type'] ?? '') === 'maasai_page');
    $maasaiData = $maasaiSection['data'] ?? [];
    $maasaiCategoryIds = collect($maasaiData['product_category_ids'] ?? [])
        ->filter(fn ($categoryId) => is_numeric($categoryId))
        ->map(fn ($categoryId) => (int) $categoryId)
        ->unique()
        ->values();
    if ($maasaiCategoryIds->isEmpty() && is_numeric($maasaiData['product_category_id'] ?? null)) {
        $maasaiCategoryIds = collect([(int) $maasaiData['product_category_id']]);
    }
    $maasaiProductLimit = $maasaiData['product_limit'] ?? 12;
    $maasaiProductLimit = is_numeric($maasaiProductLimit) ? (int) $maasaiProductLimit : 12;
    $maasaiProductLimit = max(1, min(48, $maasaiProductLimit));

    $maasaiProducts = Product::with(['category', 'categories', 'tags', 'galleryImages', 'alternateImages', 'images'])
        ->where('status', 'active')
        ->when($maasaiCategoryIds->isNotEmpty(), function ($query) use ($maasaiCategoryIds) {
            $query->where(function ($categoryQuery) use ($maasaiCategoryIds) {
                $categoryQuery
                    ->whereIn('category_id', $maasaiCategoryIds)
                    ->orWhereHas('categories', fn ($relatedCategoryQuery) => $relatedCategoryQuery->whereIn('categories.id', $maasaiCategoryIds));
            });
        }, function ($query) {
            $query->where(function ($keywordQuery) {
                $keywordQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%maasai%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%shuka%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%maasai%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%shuka%'])
                    ->orWhereHas('category', function ($categoryQuery) {
                        $categoryQuery
                            ->whereRaw('LOWER(slug) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ['%shuka%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%shuka%']);
                    })
                    ->orWhereHas('categories', function ($categoryQuery) {
                        $categoryQuery
                            ->whereRaw('LOWER(slug) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ['%shuka%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%shuka%']);
                    })
                    ->orWhereHas('tags', function ($tagQuery) {
                        $tagQuery
                            ->whereRaw('LOWER(slug) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ['%shuka%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%maasai%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%shuka%']);
                    });
            });
        })
        ->orderByDesc('created_at')
        ->take($maasaiProductLimit)
        ->get();

    return view('maasai', [
        'pageContent' => $page->content,
        'maasaiProducts' => $maasaiProducts,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('maasai');

Route::get('/jersey', function () {
    $page = Page::ensure('jersey', 'Jersey', PageDefaults::jersey());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $jerseyProducts = Product::with(['category', 'categories', 'galleryImages', 'alternateImages', 'images'])
        ->where('status', 'active')
        ->where(function ($categoryQuery) {
            $categoryQuery
                ->whereHas('category', function ($primaryCategoryQuery) {
                    $primaryCategoryQuery
                        ->whereRaw('LOWER(slug) = ?', ['jersey'])
                        ->orWhereRaw('LOWER(name) = ?', ['jersey']);
                })
                ->orWhereHas('categories', function ($relatedCategoryQuery) {
                    $relatedCategoryQuery
                        ->whereRaw('LOWER(slug) = ?', ['jersey'])
                        ->orWhereRaw('LOWER(name) = ?', ['jersey']);
                });
        })
        ->orderByDesc('created_at')
        ->take(18)
        ->get();

    return view('jersey', [
        'pageContent' => $page->content,
        'jerseyProducts' => $jerseyProducts,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('jersey');

Route::get('/3d-designer', function () {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());

    return view('jersey-designer', [
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'designerBoot' => [
            'save_url' => url('/design/save'),
            'load_url_base' => url('/design'),
            'upload_logo_url' => url('/upload-logo'),
            'catalog_url' => url('/jerseys'),
            'order_url' => url('/order'),
            'pricing' => config('jersey.pricing', []),
            'autosave_interval' => 15000,
        ],
    ]);
})->name('designer.3d');

Route::get('/jerseys', [JerseyCatalogController::class, 'index'])->name('jerseys.index');
Route::post('/upload-logo', [JerseyLogoController::class, 'store'])
    ->name('jerseys.logo.upload')
    ->middleware('throttle:10,1');
Route::post('/design/save', [JerseyDesignController::class, 'save'])
    ->name('jerseys.design.save')
    ->middleware('throttle:20,1');
Route::get('/design/{id}', [JerseyDesignController::class, 'show'])->name('jerseys.design.show');
Route::post('/order', [JerseyOrderController::class, 'store'])
    ->name('jerseys.order.store')
    ->middleware('throttle:10,1');

$shopRoute = function (?string $category = null) {
    $page = Page::ensure('shop', 'Shop', PageDefaults::shop());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $categories = Category::orderBy('name')->get();
    $selectedCategoryParam = trim((string) ($category ?? request('category', '')));
    $selectedCategory = null;
    if ($selectedCategoryParam !== '') {
        if (is_numeric($selectedCategoryParam)) {
            $selectedCategory = $categories->firstWhere('id', (int) $selectedCategoryParam);
        } else {
            $selectedCategory = $categories->first(function ($category) use ($selectedCategoryParam) {
                $needle = Str::of($selectedCategoryParam)->lower()->trim()->toString();
                $slug = Str::of((string) ($category->slug ?? ''))->lower()->trim()->toString();
                $name = Str::of((string) ($category->name ?? ''))->lower()->trim()->toString();

                return $needle === $slug || $needle === $name;
            });
        }
    }
    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $minPrice = is_numeric($minPrice) ? (float) $minPrice : null;
    $maxPrice = is_numeric($maxPrice) ? (float) $maxPrice : null;
    $search = trim((string) request('q', ''));
    $rating = request('rating', 'highest');
    $rating = in_array($rating, ['highest', 'lowest'], true) ? $rating : 'highest';
    $sort = request('sort', 'popular');
    $sort = in_array($sort, ['popular', 'newest', 'price_asc', 'price_desc', 'name_asc'], true) ? $sort : 'popular';
    $mainOrder = request('main_order');
    $mainOrder = is_numeric($mainOrder) ? max(0, min(100, (int) $mainOrder)) : 0;

    $productsQuery = Product::with(['category', 'categories', 'brand'])
        ->when($selectedCategory, function ($query) use ($selectedCategory) {
            $query->where(function ($categoryQuery) use ($selectedCategory) {
                $categoryQuery
                    ->where('category_id', $selectedCategory->id)
                    ->orWhereHas('categories', fn ($relatedCategoryQuery) => $relatedCategoryQuery->where('categories.id', $selectedCategory->id));
            });
        })
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('name', 'like', "%{$search}%");
                    });
            });
        })
        ->when($minPrice !== null, fn ($query) => $query->where('price', '>=', $minPrice))
        ->when($maxPrice !== null, fn ($query) => $query->where('price', '<=', $maxPrice))
        ->when($mainOrder > 0, fn ($query) => $query->where('stock', '>=', $mainOrder));

    match ($sort) {
        'newest' => $productsQuery->orderByDesc('created_at'),
        'price_asc' => $productsQuery->orderBy('price')->orderByDesc('created_at'),
        'price_desc' => $productsQuery->orderByDesc('price')->orderByDesc('created_at'),
        'name_asc' => $productsQuery->orderBy('name'),
        default => $productsQuery->orderByDesc('stock')->orderByDesc('created_at'),
    };

    $products = $productsQuery->paginate(9)->withQueryString();

    $fallbackProducts = collect([
        ['name' => 'Classic Tee', 'price' => 1290, 'rating' => 4.9, 'sold' => 3523],
        ['name' => 'Logo Hoodie', 'price' => 2990, 'rating' => 4.8, 'sold' => 2781],
        ['name' => 'Team Jersey', 'price' => 2490, 'rating' => 4.7, 'sold' => 1640],
        ['name' => 'Corporate Polo', 'price' => 1890, 'rating' => 4.6, 'sold' => 980],
        ['name' => 'Kids Tee', 'price' => 990, 'rating' => 4.8, 'sold' => 1210],
        ['name' => 'Event Hoodie', 'price' => 3290, 'rating' => 4.9, 'sold' => 870],
        ['name' => 'Athletic Set', 'price' => 3590, 'rating' => 4.7, 'sold' => 640],
        ['name' => 'Gift Bundle', 'price' => 2690, 'rating' => 4.6, 'sold' => 430],
    ]);
    if ($selectedCategory || $search !== '' || $minPrice !== null || $maxPrice !== null || $mainOrder > 0) {
        $fallbackProducts = $fallbackProducts->filter(function ($item) use ($selectedCategory, $search, $minPrice, $maxPrice, $mainOrder) {
            $name = Str::of((string) ($item['name'] ?? ''))->lower();
            if ($selectedCategory) {
                $categoryName = Str::of((string) $selectedCategory->name)->lower();
                $categorySlug = Str::of((string) ($selectedCategory->slug ?? ''))->lower();
                if (!$name->contains((string) $categoryName) && ($categorySlug->isEmpty() || !$name->contains((string) $categorySlug))) {
                    return false;
                }
            }
            if ($search !== '' && !$name->contains(Str::of($search)->lower()->toString())) {
                return false;
            }
            $price = (float) ($item['price'] ?? 0);
            if ($minPrice !== null && $price < $minPrice) {
                return false;
            }
            if ($maxPrice !== null && $price > $maxPrice) {
                return false;
            }
            if ($mainOrder > 0 && (int) ($item['sold'] ?? 0) < $mainOrder) {
                return false;
            }
            return true;
        })->values();
    }
    $fallbackProducts = match ($sort) {
        'price_asc' => $fallbackProducts->sortBy('price')->values(),
        'price_desc' => $fallbackProducts->sortByDesc('price')->values(),
        'name_asc' => $fallbackProducts->sortBy('name')->values(),
        'newest' => $fallbackProducts->values(),
        default => $rating === 'lowest'
            ? $fallbackProducts->sortBy('rating')->values()
            : $fallbackProducts->sortByDesc('rating')->values(),
    };

    return view('shop', [
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
        'products' => $products,
        'fallbackProducts' => $fallbackProducts,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice,
        'search' => $search,
        'rating' => $rating,
        'sort' => $sort,
        'mainOrder' => $mainOrder,
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
};

Route::get('/shop', $shopRoute)->name('shop');
Route::get('/shop/{category}', $shopRoute)->name('shop.category');

Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/blog-media/{path}', [BlogController::class, 'media'])
    ->where('path', '.*')
    ->name('blogs.media');

Route::get('/sitemap.xml', function () {
    $generatedAt = now()->toAtomString();
    $pageDefinitions = collect([
        ['slug' => 'home', 'path' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
        ['slug' => 'shop', 'path' => '/shop', 'changefreq' => 'daily', 'priority' => '0.9'],
        ['slug' => 'maasai', 'path' => '/maasai', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['slug' => 'embroidery', 'path' => '/embroidery', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['slug' => 'jersey', 'path' => '/jersey', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['slug' => 'corporate', 'path' => '/corporate', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['slug' => 'contact', 'path' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['slug' => 'mockup', 'path' => '/mockup', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['slug' => null, 'path' => '/remove-background', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['slug' => null, 'path' => '/blogs', 'changefreq' => 'daily', 'priority' => '0.8'],
        ['slug' => null, 'path' => '/jerseys', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['slug' => null, 'path' => '/3d-designer', 'changefreq' => 'weekly', 'priority' => '0.7'],
        ['slug' => null, 'path' => '/ethical-policies', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['slug' => null, 'path' => '/data-protection-policies', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['slug' => null, 'path' => '/affiliate-policies', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ]);

    $pageUpdateMap = collect();
    if (Schema::hasTable('pages')) {
        $knownSlugs = $pageDefinitions
            ->pluck('slug')
            ->filter()
            ->values();

        $pageUpdateMap = Page::query()
            ->whereIn('slug', $knownSlugs)
            ->get(['slug', 'updated_at'])
            ->keyBy('slug');
    }

    $entries = $pageDefinitions->map(function (array $definition) use ($pageUpdateMap, $generatedAt) {
        $lastModified = $generatedAt;

        if (!empty($definition['slug'])) {
            $page = $pageUpdateMap->get($definition['slug']);
            $lastModified = $page?->updated_at?->toAtomString() ?? $generatedAt;
        }

        return [
            'loc' => url($definition['path']),
            'lastmod' => $lastModified,
            'changefreq' => $definition['changefreq'],
            'priority' => $definition['priority'],
        ];
    })->values();

    if (Schema::hasTable('products')) {
        $productEntries = Product::query()
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->get(['id', 'slug', 'updated_at', 'created_at'])
            ->map(function (Product $product) {
                return [
                    'loc' => route('products.show', ['product' => $product->slug ?: $product->getKey()]),
                    'lastmod' => optional($product->updated_at ?? $product->created_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        $entries = $entries->concat($productEntries)->values();
    }

    if (Schema::hasTable('blogs')) {
        $blogEntries = Blog::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'published_at', 'created_at'])
            ->map(function (Blog $blog) {
                $lastModified = $blog->updated_at ?? $blog->published_at ?? $blog->created_at;

                return [
                    'loc' => route('blogs.show', $blog->slug),
                    'lastmod' => optional($lastModified)->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            });

        $entries = $entries->concat($blogEntries)->values();
    }

    return response()
        ->view('sitemap.xml', ['entries' => $entries])
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/checkout', function () {
    $page = Page::ensure('checkout', 'Checkout', PageDefaults::checkout());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('checkout', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('checkout');

Route::post('/checkout/submit', [CheckoutController::class, 'submit'])
    ->name('checkout.submit')
    ->middleware('throttle:10,1');

Route::prefix('affiliate')->name('affiliate.')->group(function () {
    Route::get('/register', [AffiliateAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AffiliateAuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [AffiliateAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AffiliateAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AffiliateAuthController::class, 'logout'])->name('logout');

    Route::middleware('affiliate.auth')->group(function () {
        Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])->name('dashboard');
        Route::post('/payouts', [AffiliateDashboardController::class, 'requestPayout'])->name('payouts.store');
    });
});

Route::get('/corporate', function () {
    $page = Page::ensure('corporate', 'Corporate', PageDefaults::corporate());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('corporate', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('corporate');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:5,1');

Route::get('/ethical-policies', function () {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('ethical-policies', [
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('ethical-policies');

Route::get('/data-protection-policies', function () {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('data-protection-policies', [
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('data-protection-policies');

Route::get('/affiliate-policies', function () {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('affiliate-policies', [
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('affiliate-policies');

Route::get('/remove-background', function () {
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('remove-background', [
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('remove-background');

Route::post('/newsletter', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email:rfc,dns'],
    ]);

    return back()->with('newsletter_subscribed', true);
})->name('newsletter.subscribe')->middleware('throttle:5,1');

Route::post('/corporate/quote', [CorporateQuoteController::class, 'store'])
    ->name('corporate.quote')
    ->middleware('throttle:5,1');

Route::get('/mockup', function () {
    $page = Page::ensure('mockup', 'Mockup', PageDefaults::mockup());
    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);

    return view('mockup', [
        'pageContent' => $page->content,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('mockup');

Route::post('/mockup/ai/generate', [MockupAiController::class, 'generate'])
    ->name('mockup.ai.generate')
    ->middleware('throttle:6,1');

Route::post('/mockup/checkout', [MockupCheckoutController::class, 'store'])
    ->name('mockup.checkout')
    ->middleware('throttle:8,1');

Route::get('/products/{product}', function (string $productKey) {
    $product = Product::query()
        ->where('slug', $productKey)
        ->when(ctype_digit($productKey), fn ($query) => $query->orWhere('id', (int) $productKey))
        ->firstOrFail();

    $header = GlobalSection::ensure('header', PageDefaults::header());
    $footer = GlobalSection::ensure('footer', PageDefaults::footer());
    $purchasePopups = GlobalSection::ensure('purchase_popups', PageDefaults::purchasePopups());
    $purchasePopupProducts = resolvePurchasePopupProducts($purchasePopups->content ?? []);
    $product->load(['category', 'categories', 'brand', 'alternateImages', 'galleryImages', 'attributes', 'sizes', 'tags']);
    $productCategoryIdentity = $product->categories
        ->push($product->category)
        ->filter()
        ->map(fn ($category) => strtolower(trim(($category->slug ?? '') . ' ' . ($category->name ?? ''))))
        ->join(' ');
    if (str_contains($productCategoryIdentity, 'maasai') && Schema::hasTable('product_selection_images')) {
        $product->load('selectionImages');
    }
    $relatedCategoryIds = $product->categories
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->when($product->category_id, fn ($ids) => $ids->push((int) $product->category_id))
        ->unique()
        ->values()
        ->all();

    $relatedProducts = Product::with(['category', 'categories', 'alternateImages', 'galleryImages'])
        ->where('id', '!=', $product->id)
        ->when(!empty($relatedCategoryIds), function ($query) use ($relatedCategoryIds) {
            $query->where(function ($relatedCategoryQuery) use ($relatedCategoryIds) {
                $relatedCategoryQuery
                    ->whereIn('category_id', $relatedCategoryIds)
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->whereIn('categories.id', $relatedCategoryIds));
            });
        })
        ->orderByDesc('created_at')
        ->take(4)
        ->get();

    if ($relatedProducts->count() < 4) {
        $moreProducts = Product::with(['category', 'categories', 'alternateImages', 'galleryImages'])
            ->where('id', '!=', $product->id)
            ->whereNotIn('id', $relatedProducts->pluck('id'))
            ->orderByDesc('created_at')
            ->take(4 - $relatedProducts->count())
            ->get();
        $relatedProducts = $relatedProducts->concat($moreProducts);
    }

    return view('product', [
        'product' => $product,
        'relatedProducts' => $relatedProducts,
        'globalHeader' => $header->content,
        'globalFooter' => $footer->content,
        'globalPurchasePopups' => $purchasePopups->content,
        'globalPurchasePopupProducts' => $purchasePopupProducts,
    ]);
})->name('products.show');

Route::get('/invoice/view/{invoice:public_id}', [PublicInvoiceController::class, 'show'])
    ->name('invoice.public');
Route::get('/invoice/view/{invoice:public_id}/pdf', [PublicInvoiceController::class, 'pdf'])
    ->name('invoice.public.pdf');
Route::get('/invoice/view/{invoice:public_id}/status', [PublicInvoiceController::class, 'status'])
    ->name('invoice.payment.status')
    ->middleware('throttle:30,1');
Route::get('/invoice/verify/{invoice:public_id}', [PublicInvoiceController::class, 'verify'])
    ->name('invoice.verify')
    ->middleware('signed');
Route::post('/invoice/view/{invoice:public_id}/mpesa', [PublicMpesaController::class, 'pay'])
    ->name('invoice.public.mpesa')
    ->middleware('throttle:5,1');
Route::get('/receipt/view/{receipt:public_id}', [PublicReceiptController::class, 'show'])
    ->name('receipt.public');
Route::get('/receipt/view/{receipt:public_id}/pdf', [PublicReceiptController::class, 'pdf'])
    ->name('receipt.public.pdf');

Route::post('/mpesa/stk/callback', [MpesaCallbackController::class, 'stkCallback'])
    ->name('mpesa.stk.callback')
    ->middleware(['mpesa.callback', 'throttle:120,1'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/mpesa/c2b/validation', [MpesaCallbackController::class, 'c2bValidation'])
    ->name('mpesa.c2b.validation')
    ->middleware(['mpesa.callback', 'throttle:120,1'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/mpesa/c2b/confirmation', [MpesaCallbackController::class, 'c2bConfirmation'])
    ->name('mpesa.c2b.confirmation')
    ->middleware(['mpesa.callback', 'throttle:120,1'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $username = trim((string) $credentials['username']);
    $expectedUser = (string) config('admin.username', 'Admin');
    $expectedHash = trim((string) config('admin.password_hash', ''));

    if (
        $expectedHash !== '' &&
        hash_equals($expectedUser, $username) &&
        Hash::check($credentials['password'], $expectedHash)
    ) {
        $request->session()->put([
            'admin_authenticated' => true,
            'admin_role' => 'admin',
            'admin_user_id' => null,
            'admin_name' => (string) $expectedUser,
        ]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    $supportsRoleBasedAdmin = Schema::hasTable('users') && Schema::hasColumn('users', 'role');
    $editor = null;

    if ($supportsRoleBasedAdmin) {
        $editor = User::query()
            ->where(function ($query) use ($username) {
                $query->where('email', $username);

                if (!str_contains($username, '@')) {
                    $query->orWhere('name', $username);
                }
            })
            ->whereIn('role', ['admin', 'editor'])
            ->first();
    }

    if ($editor && Hash::check($credentials['password'], (string) $editor->password)) {
        $role = strtolower((string) $editor->role);
        $request->session()->put([
            'admin_authenticated' => true,
            'admin_role' => $role,
            'admin_user_id' => (int) $editor->id,
            'admin_name' => (string) ($editor->name ?: $editor->email),
        ]);
        $request->session()->regenerate();

        if ($role === 'editor') {
            return redirect()->route('admin.products.index');
        }

        return redirect()->route('admin.dashboard');
    }

    if (!$supportsRoleBasedAdmin && str_contains($username, '@')) {
        return back()->withErrors([
            'username' => 'Editor login is not available yet. Run migrations to enable role-based admin accounts.',
        ])->withInput();
    }

    return back()->withErrors(['username' => 'Invalid credentials.'])->withInput();
})->name('admin.login.submit')->middleware('throttle:5,1');

Route::post('/admin/logout', function (Request $request) {
    $request->session()->forget(['admin_authenticated', 'admin_role', 'admin_user_id', 'admin_name']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
})->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::post('/performance/reveal', function (Request $request) {
        abort_unless(session('admin_role') === 'admin', 403);

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $password = (string) $validated['password'];
        $userId = $request->session()->get('admin_user_id');
        $verified = false;

        if ($userId && Schema::hasTable('users')) {
            $admin = User::query()->find($userId);
            $verified = $admin
                && strtolower((string) $admin->role) === 'admin'
                && Hash::check($password, (string) $admin->password);
        }

        if (!$verified && !$userId) {
            $expectedHash = trim((string) config('admin.password_hash', ''));
            $verified = $expectedHash !== '' && Hash::check($password, $expectedHash);
        }

        if (!$verified) {
            return response()->json([
                'message' => 'Invalid admin password.',
            ], 422);
        }

        return response()->json([
            'revealed' => true,
        ]);
    })->name('performance.reveal')->middleware(['admin.can:admin', 'throttle:6,1']);

    Route::post('/financial-entries', function (Request $request) {
        abort_unless(session('admin_role') === 'admin', 403);
        abort_unless(Schema::hasTable('financial_entries'), 404);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'category' => ['required', 'in:production_cost,materials_bought,petty_cash'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        FinancialEntry::create($validated);

        $dashboardQuery = $request->only(['analytics_period', 'analytics_start', 'analytics_end']);
        if ($request->filled('dashboard_category')) {
            $dashboardQuery['category'] = $request->input('dashboard_category');
        }

        return redirect()
            ->route('admin.dashboard', $dashboardQuery)
            ->with('success', 'Cost entry recorded.');
    })->name('financial-entries.store')->middleware('admin.can:admin');

    Route::put('/financial-entries/{financialEntry}', function (Request $request, FinancialEntry $financialEntry) {
        abort_unless(session('admin_role') === 'admin', 403);
        abort_unless(Schema::hasTable('financial_entries'), 404);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'category' => ['required', 'in:production_cost,materials_bought,petty_cash'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $financialEntry->update($validated);

        $dashboardQuery = $request->only(['analytics_period', 'analytics_start', 'analytics_end']);
        if ($request->filled('dashboard_category')) {
            $dashboardQuery['category'] = $request->input('dashboard_category');
        }

        return redirect()
            ->route('admin.dashboard', $dashboardQuery)
            ->with('success', 'Cost entry updated.');
    })->name('financial-entries.update')->middleware('admin.can:admin');

    Route::get('/', function () {
        if (session('admin_role') === 'editor') {
            return redirect()->route('admin.products.index');
        }

        $categoryId = request('category');

        $products = Product::with(['category', 'categories', 'brand'])
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where(function ($categoryQuery) use ($categoryId) {
                    $categoryQuery
                        ->where('category_id', $categoryId)
                        ->orWhereHas('categories', fn ($relatedCategoryQuery) => $relatedCategoryQuery->where('categories.id', $categoryId));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $featuredProduct = $products->first();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $isTaskModuleReady = Schema::hasTable('tasks');
        $isPaymentModuleReady = Schema::hasTable('payments') && Schema::hasTable('invoices');
        $isFinanceModuleReady = Schema::hasTable('financial_entries');
        $taskStats = [
            'total' => 0,
            'assigned' => 0,
            'in_progress' => 0,
            'finished' => 0,
            'delivered' => 0,
        ];
        $recentTasks = collect();
        $analyticsPeriod = request('analytics_period', 'monthly');
        $allowedAnalyticsPeriods = ['daily', 'weekly', 'monthly', 'yearly', 'custom'];
        $analyticsPeriod = in_array($analyticsPeriod, $allowedAnalyticsPeriods, true) ? $analyticsPeriod : 'monthly';
        $today = Carbon::today();
        $customStart = request('analytics_start');
        $customEnd = request('analytics_end');
        $parseAnalyticsDate = function ($value, Carbon $fallback): Carbon {
            if (!is_string($value) || trim($value) === '') {
                return $fallback->copy();
            }

            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return $fallback->copy();
            }
        };

        [$analyticsStart, $analyticsEnd, $analyticsGroup] = match ($analyticsPeriod) {
            'daily' => [$today->copy()->startOfDay(), $today->copy()->endOfDay(), 'hour'],
            'weekly' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek(), 'day'],
            'yearly' => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'month'],
            'custom' => [
                $parseAnalyticsDate($customStart, $today->copy()->subDays(29))->startOfDay(),
                $parseAnalyticsDate($customEnd, $today)->endOfDay(),
                'day',
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'day'],
        };

        if ($analyticsStart->greaterThan($analyticsEnd)) {
            [$analyticsStart, $analyticsEnd] = [$analyticsEnd->copy()->startOfDay(), $analyticsStart->copy()->endOfDay()];
        }

        if ($analyticsPeriod === 'custom' && $analyticsStart->diffInDays($analyticsEnd) > 90) {
            $analyticsGroup = 'month';
        }

        $analyticsCurrency = 'KES';
        $analyticsRows = collect();
        $financeRows = collect();
        $analyticsSummary = [
            'revenue' => 0,
            'orders' => 0,
            'transactions' => 0,
            'average_transaction' => 0,
            'credits' => 0,
            'debits' => 0,
            'net' => 0,
            'production_cost' => 0,
            'materials_bought' => 0,
            'petty_cash' => 0,
        ];
        $analyticsTrend = [];
        $categoryLabels = [
            'production_cost' => 'Production Cost',
            'materials_bought' => 'Materials Bought',
            'petty_cash' => 'Petty Cash',
        ];

        if ($isPaymentModuleReady) {
            $analyticsRows = Payment::with('invoice:id,currency')
                ->where(function ($query) use ($analyticsStart, $analyticsEnd) {
                    $query->whereBetween('payment_date', [$analyticsStart->toDateString(), $analyticsEnd->toDateString()])
                        ->orWhere(function ($createdAtQuery) use ($analyticsStart, $analyticsEnd) {
                            $createdAtQuery->whereNull('payment_date')
                                ->whereBetween('created_at', [$analyticsStart, $analyticsEnd]);
                        });
                })
                ->get();

            $analyticsCurrency = $analyticsRows
                ->pluck('invoice.currency')
                ->filter()
                ->first() ?? Invoice::query()->latest()->value('currency') ?? 'KES';

            $analyticsSummary['revenue'] = round((float) $analyticsRows->sum('amount'), 2);
            $analyticsSummary['orders'] = $analyticsRows->pluck('invoice_id')->filter()->unique()->count();
            $analyticsSummary['transactions'] = $analyticsRows->count();
            $analyticsSummary['average_transaction'] = $analyticsSummary['transactions'] > 0
                ? round($analyticsSummary['revenue'] / $analyticsSummary['transactions'], 2)
                : 0;
        }

        if ($isFinanceModuleReady) {
            $financeRows = FinancialEntry::query()
                ->whereBetween('entry_date', [$analyticsStart->toDateString(), $analyticsEnd->toDateString()])
                ->orderByDesc('entry_date')
                ->orderByDesc('created_at')
                ->get();

            $analyticsSummary['production_cost'] = round((float) $financeRows->where('category', 'production_cost')->sum('amount'), 2);
            $analyticsSummary['materials_bought'] = round((float) $financeRows->where('category', 'materials_bought')->sum('amount'), 2);
            $analyticsSummary['petty_cash'] = round((float) $financeRows->where('category', 'petty_cash')->sum('amount'), 2);
            $analyticsSummary['debits'] = round((float) $financeRows->sum('amount'), 2);
        }

        $analyticsSummary['credits'] = $analyticsSummary['revenue'];
        $analyticsSummary['net'] = round($analyticsSummary['credits'] - $analyticsSummary['debits'], 2);
        $largestCost = $financeRows->sortByDesc(fn ($entry) => (float) $entry->amount)->first();
        $costBreakdown = collect($categoryLabels)
            ->map(function ($label, $category) use ($financeRows, $analyticsSummary) {
                $rows = $financeRows->where('category', $category);
                $amount = round((float) $rows->sum('amount'), 2);

                return [
                    'category' => $category,
                    'label' => $label,
                    'amount' => $amount,
                    'count' => $rows->count(),
                    'percent' => $analyticsSummary['debits'] > 0
                        ? round(($amount / $analyticsSummary['debits']) * 100, 1)
                        : 0,
                ];
            })
            ->values();
        $highestCategory = $costBreakdown->sortByDesc('amount')->first();
        $costAnalysis = [
            'count' => $financeRows->count(),
            'average' => $financeRows->count() > 0
                ? round((float) $financeRows->sum('amount') / $financeRows->count(), 2)
                : 0,
            'highest_category' => $highestCategory,
            'largest_entry' => $largestCost ? [
                'label' => $largestCost->category_label,
                'description' => $largestCost->description,
                'amount' => round((float) $largestCost->amount, 2),
                'date' => optional($largestCost->entry_date)->format('M d, Y'),
            ] : null,
            'breakdown' => $costBreakdown,
        ];

        $cursor = $analyticsStart->copy();
        while ($cursor->lessThanOrEqualTo($analyticsEnd)) {
            $key = match ($analyticsGroup) {
                'hour' => $cursor->format('Y-m-d H'),
                'month' => $cursor->format('Y-m'),
                default => $cursor->format('Y-m-d'),
            };

            $label = match ($analyticsGroup) {
                'hour' => $cursor->format('g A'),
                'month' => $cursor->format('M Y'),
                default => $cursor->format('M j'),
            };

            $analyticsTrend[$key] = [
                'label' => $label,
                'value' => 0,
            ];

            match ($analyticsGroup) {
                'hour' => $cursor->addHour(),
                'month' => $cursor->addMonthNoOverflow()->startOfMonth(),
                default => $cursor->addDay(),
            };
        }

        if ($isPaymentModuleReady) {
            foreach ($analyticsRows as $payment) {
                $paymentDate = $payment->payment_date
                    ? Carbon::parse($payment->payment_date)
                    : Carbon::parse($payment->created_at);
                $key = match ($analyticsGroup) {
                    'hour' => $paymentDate->format('Y-m-d H'),
                    'month' => $paymentDate->format('Y-m'),
                    default => $paymentDate->format('Y-m-d'),
                };

                if (isset($analyticsTrend[$key])) {
                    $analyticsTrend[$key]['value'] += (float) $payment->amount;
                }
            }
        }

        $analyticsTrend = array_values($analyticsTrend);
        $analytics = [
            'period' => $analyticsPeriod,
            'start' => $analyticsStart->toDateString(),
            'end' => $analyticsEnd->toDateString(),
            'group' => $analyticsGroup,
            'currency' => $analyticsCurrency,
            'locale' => str_replace('_', '-', app()->getLocale()),
            'summary' => $analyticsSummary,
            'trend' => $analyticsTrend,
            'is_ready' => $isPaymentModuleReady,
            'finance_is_ready' => $isFinanceModuleReady,
            'recent_finance_entries' => $financeRows,
            'cost_analysis' => $costAnalysis,
        ];

        if ($isTaskModuleReady) {
            $taskStats = [
                'total' => Task::count(),
                'assigned' => Task::where('status', 'assigned')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'finished' => Task::where('status', 'finished')->count(),
                'delivered' => Task::where('status', 'delivered')->count(),
            ];
            $recentTasks = Task::with('client')
                ->orderByDesc('updated_at')
                ->take(6)
                ->get();
        }

        return view('admin.dashboard', compact('products', 'featuredProduct', 'categories', 'brands', 'taskStats', 'recentTasks', 'isTaskModuleReady', 'analytics'));
    })->name('dashboard')->middleware('admin.can:admin');

    Route::middleware('admin.can:admin,editor')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
    });

    Route::middleware('admin.can:admin')->group(function () {
        Route::post('blogs/editor/upload-image', [AdminBlogController::class, 'uploadEditorImage'])->name('blogs.editor.upload-image');
        Route::post('blogs/editor/upload-image-url', [AdminBlogController::class, 'uploadEditorImageUrl'])->name('blogs.editor.upload-image-url');
        Route::post('blogs/{blog}/publish', [AdminBlogController::class, 'publish'])->name('blogs.publish');
        Route::get('blogs/{blog}', [AdminBlogController::class, 'edit'])
            ->whereNumber('blog')
            ->name('blogs.edit.alias');
        Route::resource('blogs', AdminBlogController::class)->except(['show']);
        Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
        Route::resource('blog-posters', BlogPosterController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);
        Route::resource('attributes', AttributeController::class)->except(['show']);
        Route::resource('sizes', SizeController::class)->except(['show']);
        Route::resource('media', MediaAssetController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('clients', ClientController::class)->except(['show']);
        Route::get('affiliates/export', [AdminAffiliateController::class, 'export'])->name('affiliates.export');
        Route::post('affiliates/settings', [AdminAffiliateController::class, 'settings'])->name('affiliates.settings');
        Route::post('affiliates/{affiliate}/approve', [AdminAffiliateController::class, 'approve'])->name('affiliates.approve');
        Route::post('affiliates/{affiliate}/reject', [AdminAffiliateController::class, 'reject'])->name('affiliates.reject');
        Route::put('affiliates/{affiliate}', [AdminAffiliateController::class, 'update'])->name('affiliates.update');
        Route::post('affiliate-commissions/{commission}', [AdminAffiliateController::class, 'updateCommission'])->name('affiliate-commissions.update');
        Route::post('affiliate-payouts/{payout}', [AdminAffiliateController::class, 'updatePayout'])->name('affiliate-payouts.update');
        Route::get('affiliates/{affiliate}', [AdminAffiliateController::class, 'show'])->name('affiliates.show');
        Route::get('affiliates', [AdminAffiliateController::class, 'index'])->name('affiliates.index');
        Route::get('tasks/{task}/work-note', [TaskController::class, 'downloadWorkNote'])->name('tasks.work-note');
        Route::post('tasks/{task}/next-stage', [TaskController::class, 'nextStage'])->name('tasks.next-stage');
        Route::resource('tasks', TaskController::class)->except(['show']);
        Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::get('invoices/trash', [InvoiceController::class, 'trash'])->name('invoices.trash');
        Route::post('invoices/{invoice}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
        Route::delete('invoices/{invoice}/force', [InvoiceController::class, 'forceDelete'])->name('invoices.force');
        Route::get('invoices/settings', [InvoiceSettingController::class, 'edit'])->name('invoices.settings');
        Route::post('invoices/settings', [InvoiceSettingController::class, 'update'])->name('invoices.settings.update');
        Route::post('invoices/settings/mpesa/test', [MpesaController::class, 'testCredentials'])->name('invoices.mpesa.test');
        Route::post('invoices/settings/mpesa/register', [MpesaController::class, 'registerC2b'])->name('invoices.mpesa.register');
        Route::get('invoices/{invoice}/payments', fn (Invoice $invoice) => redirect()->route('admin.invoices.show', $invoice))
            ->name('invoices.payments.index');
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('invoices.payments.store');
        Route::post('invoices/{invoice}/mpesa/stk', [MpesaController::class, 'stkPush'])->name('invoices.mpesa.stk');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoices.send');
        Route::resource('invoices', InvoiceController::class);
        Route::get('quotations/export', [QuotationController::class, 'export'])->name('quotations.export');
        Route::get('quotations/trash', [QuotationController::class, 'trash'])->name('quotations.trash');
        Route::post('quotations/{quotation}/restore', [QuotationController::class, 'restore'])->name('quotations.restore');
        Route::delete('quotations/{quotation}/force', [QuotationController::class, 'forceDelete'])->name('quotations.force');
        Route::get('quotations/{quotation}/download', [QuotationController::class, 'download'])->name('quotations.download');
        Route::post('quotations/{quotation}/send', [QuotationController::class, 'sendEmail'])->name('quotations.send');
        Route::resource('quotations', QuotationController::class);
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');

        Route::get('builder', [PageBuilderController::class, 'index'])->name('builder.index');
        Route::post('builder/globals', [PageBuilderController::class, 'updateGlobals'])->name('builder.globals');
        Route::get('builder/{page}', [PageBuilderController::class, 'edit'])->name('builder.edit');
        Route::put('builder/{page}', [PageBuilderController::class, 'update'])->name('builder.update');
    });
});
