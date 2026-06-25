<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => ($selectedCategory?->name ? $selectedCategory->name . ' | ' : '') . 'Shop Custom Apparel | Nai Prints',
            'description' => $selectedCategory?->name
                ? 'Shop custom ' . $selectedCategory->name . ' printing from Nai Prints in Kenya. Browse products, compare prices, and order online.'
                : 'Browse custom t-shirts, hoodies, polos, jerseys, gifts, and branded apparel from Nai Prints in Kenya.',
            'canonical' => $selectedCategory ? route('shop.category', ['category' => $selectedCategory->slug ?: $selectedCategory->id]) : route('shop'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-8">
                @php
                    $shopHeader = collect($pageContent['sections'] ?? [])->firstWhere('type', 'shop_header');
                    $shopHeaderData = $shopHeader['data'] ?? [];
                    $categoryRouteKey = static fn ($category) => $category ? ($category->slug ?: $category->id) : null;
                    $filterQuery = static function (array $overrides = []) use ($search, $rating, $sort, $mainOrder, $minPrice, $maxPrice) {
                        $query = array_merge([
                            'q' => $search ?: null,
                            'rating' => $rating !== 'highest' ? $rating : null,
                            'sort' => $sort !== 'popular' ? $sort : null,
                            'main_order' => $mainOrder > 0 ? $mainOrder : null,
                            'min_price' => $minPrice !== null ? $minPrice : null,
                            'max_price' => $maxPrice !== null ? $maxPrice : null,
                        ], $overrides);

                        return collect($query)
                            ->reject(fn ($value) => $value === null || $value === '')
                            ->all();
                    };
                    $shopUrl = static function ($category = null, array $overrides = []) use ($categoryRouteKey, $filterQuery) {
                        $baseUrl = $category
                            ? route('shop.category', ['category' => $categoryRouteKey($category)])
                            : route('shop');
                        $query = $filterQuery($overrides);

                        return $query ? $baseUrl . '?' . http_build_query($query) : $baseUrl;
                    };
                    $currentShopAction = $selectedCategory
                        ? route('shop.category', ['category' => $categoryRouteKey($selectedCategory)])
                        : route('shop');
                @endphp
                @if (!empty($shopHeaderData))
                    <div class="mb-6">
                        <p class="section-kicker">{{ $shopHeaderData['subtitle'] ?? '' }}</p>
                        <h1 class="section-title">{{ $shopHeaderData['title'] ?? '' }}</h1>
                    </div>
                @endif

                <div class="shop-topbar">
                    <form class="shop-search" method="GET" action="{{ $currentShopAction }}">
                        @if ($rating !== 'highest')
                            <input type="hidden" name="rating" value="{{ $rating }}">
                        @endif
                        @if ($sort !== 'popular')
                            <input type="hidden" name="sort" value="{{ $sort }}">
                        @endif
                        @if ($mainOrder > 0)
                            <input type="hidden" name="main_order" value="{{ $mainOrder }}">
                        @endif
                        @if ($minPrice !== null)
                            <input type="hidden" name="min_price" value="{{ $minPrice }}">
                        @endif
                        @if ($maxPrice !== null)
                            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                        @endif
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.03 4.11l3.43 3.43a.75.75 0 1 1-1.06 1.06l-3.43-3.43A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search products..." class="shop-search-input">
                        <button class="shop-search-btn">Search</button>
                    </form>
                    <div class="shop-actions">
                        <button class="icon-button relative" aria-label="Cart" data-cart-button>
                            <span class="badge" data-cart-count>0</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M1.5 3.5a.75.75 0 0 1 .75-.75H4a.75.75 0 0 1 .72.54l.5 1.71h10.82a.75.75 0 0 1 .72.96l-1.6 5.6a.75.75 0 0 1-.72.54H7.05l.3 1.05h7.9a.75.75 0 0 1 0 1.5H6.7a.75.75 0 0 1-.72-.54L4.14 5.25H2.25a.75.75 0 0 1-.75-.75Z" />
                            </svg>
                        </button>
                        <button class="icon-button" aria-label="Notifications">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 2.5a4.5 4.5 0 0 0-4.5 4.5v2.2c0 .5-.2 1-.6 1.4l-1.1 1.1a.75.75 0 0 0 .53 1.28h11.34a.75.75 0 0 0 .53-1.28l-1.1-1.1c-.4-.4-.6-.9-.6-1.4V7A4.5 4.5 0 0 0 10 2.5Z" />
                                <path d="M7.5 15.5a2.5 2.5 0 0 0 5 0h-5Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="shop-layout">
                    <aside class="shop-filters">
                        <form class="shop-filter-card" method="GET" action="{{ $currentShopAction }}">
                            <h3 class="text-lg font-semibold">Filters</h3>
                            <div class="mt-6 space-y-6 text-sm text-slate-600">
                                <div>
                                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                        <span>Categories</span>
                                        <span>▾</span>
                                    </div>
                                    <ul class="mt-3 space-y-2">
                                        <li>
                                            <a href="{{ $shopUrl(null) }}"
                                               class="shop-category-link {{ empty($selectedCategory) ? 'is-active' : '' }}"
                                               aria-current="{{ empty($selectedCategory) ? 'page' : 'false' }}">
                                                <span class="shop-category-radio" aria-hidden="true"></span>
                                                <span>All categories</span>
                                            </a>
                                        </li>
                                        @forelse ($categories as $category)
                                            @php
                                                $isActiveCategory = (string) ($selectedCategory->id ?? '') === (string) $category->id;
                                            @endphp
                                            <li>
                                                <a href="{{ $shopUrl($category) }}"
                                                   class="shop-category-link {{ $isActiveCategory ? 'is-active' : '' }}"
                                                   aria-current="{{ $isActiveCategory ? 'page' : 'false' }}">
                                                    <span class="shop-category-radio" aria-hidden="true"></span>
                                                    <span>{{ $category->name }}</span>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-slate-500">No categories yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                        <span>Rating</span>
                                        <span>▾</span>
                                    </div>
                                    <ul class="mt-3 space-y-2">
                                        <li class="flex items-center gap-2">
                                            <input type="radio" name="rating" value="highest" class="h-4 w-4 rounded border-slate-300" @checked($rating === 'highest')>
                                            <span>Highest</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <input type="radio" name="rating" value="lowest" class="h-4 w-4 rounded border-slate-300" @checked($rating === 'lowest')>
                                            <span>Lowest</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                        <span>Main Order</span>
                                        <span>▾</span>
                                    </div>
                                    <div class="mt-3">
                                        <input type="range" name="main_order" class="w-full accent-red-500" min="0" max="100" value="{{ $mainOrder }}">
                                        <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                            <span>0</span>
                                            <span>100</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                        <span>Price</span>
                                        <span>▾</span>
                                    </div>
                                    <div class="mt-3 grid gap-3">
                                        <input type="number"
                                               name="min_price"
                                               min="0"
                                               value="{{ $minPrice ?? '' }}"
                                               placeholder="Minimum (KSh)"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="number"
                                               name="max_price"
                                               min="0"
                                               value="{{ $maxPrice ?? '' }}"
                                               placeholder="Maximum (KSh)"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                            @if ($search !== '')
                                <input type="hidden" name="q" value="{{ $search }}">
                            @endif
                            @if ($sort !== 'popular')
                                <input type="hidden" name="sort" value="{{ $sort }}">
                            @endif
                            <div class="mt-6 flex items-center gap-3">
                                <button type="submit" class="admin-btn-primary">Apply</button>
                                <a href="{{ route('shop') }}" class="admin-btn-outline">Reset</a>
                            </div>
                        </form>
                    </aside>

                    <section class="shop-results">
                        <div class="shop-result-header">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="shop-chip">Ready to ship</span>
                                <span class="shop-chip">Paid samples</span>
                                @if (!empty($minPrice))
                                    <span class="shop-chip">Min: KSh {{ number_format($minPrice, 0) }}</span>
                                @endif
                                @if (!empty($maxPrice))
                                    <span class="shop-chip">Max: KSh {{ number_format($maxPrice, 0) }}</span>
                                @endif
                                @if (!empty($search))
                                    <span class="shop-chip">Search: {{ $search }}</span>
                                @endif
                                @if ($mainOrder > 0)
                                    <span class="shop-chip">Stock: {{ $mainOrder }}+</span>
                                @endif
                                @if (!empty($selectedCategory))
                                    <span class="shop-chip">Category: {{ $selectedCategory->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <span>Sort by:</span>
                                <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                        onchange="window.location.href = this.value">
                                    <option value="{{ $shopUrl($selectedCategory, ['sort' => null]) }}" @selected($sort === 'popular')>Popular</option>
                                    <option value="{{ $shopUrl($selectedCategory, ['sort' => 'newest']) }}" @selected($sort === 'newest')>Newest</option>
                                    <option value="{{ $shopUrl($selectedCategory, ['sort' => 'price_asc']) }}" @selected($sort === 'price_asc')>Price: Low to High</option>
                                    <option value="{{ $shopUrl($selectedCategory, ['sort' => 'price_desc']) }}" @selected($sort === 'price_desc')>Price: High to Low</option>
                                    <option value="{{ $shopUrl($selectedCategory, ['sort' => 'name_asc']) }}" @selected($sort === 'name_asc')>Name: A to Z</option>
                                </select>
                            </div>
                        </div>

                        <div class="shop-grid">
                            @php
                                $hasRealProducts = method_exists($products, 'total')
                                    ? $products->total() > 0
                                    : $products->count() > 0;
                                $shopProducts = $hasRealProducts ? $products : $fallbackProducts;
                            @endphp
                            @forelse ($shopProducts as $product)
                                @php
                                    $isModel = is_object($product);
                                    $name = $product['name'] ?? $product->name;
                                    $price = $product['price'] ?? $product->price;
                                    $rating = $product['rating'] ?? 4.8;
                                    $sold = $product['sold'] ?? rand(120, 3600);
                                    $imageUrl = $isModel ? ($product->image_url ?? null) : ($product['image_url'] ?? null);
                                    $rawPrice = is_numeric($price) ? (float) $price : (float) preg_replace('/[^\d.]/', '', (string) $price);
                                    $detailUrl = $isModel && isset($product->id)
                                        ? route('products.show', ['product' => ($product->slug ?: $product->id)])
                                        : null;
                                    $productId = $isModel && isset($product->id) ? $product->id : ('fallback-' . md5($name));
                                    $brandName = $isModel ? ($product->brand?->name ?? 'Nai Prints') : 'Nai Prints';
                                    $categoryName = $isModel ? ($product->category?->name ?? 'Apparel') : 'Apparel';
                                    $safeName = e((string) $name);
                                    $flameName = preg_replace('/\\bT[-\\s]?shirt\\b/i', '<span class=\"flame-text\">$0</span>', $safeName);
                                @endphp
                                <article class="shop-card">
                                    <div class="shop-card-media">
                                        @if ($detailUrl)
                                            <a href="{{ $detailUrl }}" class="block h-full w-full">
                                        @endif
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="shop-card-placeholder">
                                                <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        @if ($detailUrl)
                                            </a>
                                        @endif
                                    </div>
                                    <div class="mt-4 space-y-3">
                                        <div class="flex items-center justify-between text-xs text-slate-500">
                                            <span>{{ $brandName }}</span>
                                            <span>{{ $categoryName }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                @if ($detailUrl)
                                                    <a href="{{ $detailUrl }}" class="text-sm font-semibold text-slate-700 hover:text-brand">
                                                        {!! $flameName !!}
                                                    </a>
                                                @else
                                                    <p class="text-sm font-semibold text-slate-700">
                                                        {!! $flameName !!}
                                                    </p>
                                                @endif
                                                <p class="text-lg font-semibold">KSh {{ number_format($rawPrice, 0) }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button class="add-button wishlist-button" type="button"
                                                        aria-label="Add to wishlist"
                                                        data-wishlist-toggle
                                                        data-product-id="{{ $productId }}"
                                                        data-product-name="{{ $name }}"
                                                        data-product-price="{{ $rawPrice }}"
                                                        data-product-image="{{ $imageUrl }}"
                                                        data-product-url="{{ $detailUrl }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 20.5s-7-4.35-9.33-8.04A5.5 5.5 0 0 1 12 5.2a5.5 5.5 0 0 1 9.33 7.26C19 16.15 12 20.5 12 20.5Z" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                    </svg>
                                                </button>
                                                <button class="add-button" type="button"
                                                        data-add-to-cart
                                                        data-product-id="{{ $productId }}"
                                                        data-product-name="{{ $name }}"
                                                        data-product-price="{{ $rawPrice }}"
                                                        data-product-image="{{ $imageUrl }}"
                                                        data-product-url="{{ $detailUrl }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path d="M10 4.5a.75.75 0 0 1 .75.75v4h4a.75.75 0 0 1 0 1.5h-4v4a.75.75 0 0 1-1.5 0v-4h-4a.75.75 0 0 1 0-1.5h4v-4A.75.75 0 0 1 10 4.5Z" />
                                                    </svg>
                                                    <span class="sr-only">Add {{ $name }} to cart</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-slate-500">
                                            <span>Star {{ number_format($rating, 1) }}</span>
                                            <span>{{ $sold }} sold</span>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="shop-empty">
                                    <p class="text-sm font-semibold text-slate-700">No products found.</p>
                                    <a href="{{ route('shop') }}" class="mt-2 inline-flex text-sm font-semibold text-brand">Clear filters</a>
                                </div>
                            @endforelse
                        </div>

                        @if (method_exists($products, 'hasPages') && $products->hasPages())
                            <div class="mt-6">
                                {{ $products->links() }}
                            </div>
                        @endif
                    </section>
                </div>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
