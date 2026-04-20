<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = $globalHeader['favicon_url'] ?? '';
            $faviconUrl = $faviconUrl ?: '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Shop | Nai Prints</title>
        <meta name="description" content="Browse custom tees, hoodies, polos, and more on Nai Prints.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-8">
                @php
                    $shopHeader = collect($pageContent['sections'] ?? [])->firstWhere('type', 'shop_header');
                    $shopHeaderData = $shopHeader['data'] ?? [];
                @endphp
                @if (!empty($shopHeaderData))
                    <div class="mb-6">
                        <p class="section-kicker">{{ $shopHeaderData['subtitle'] ?? '' }}</p>
                        <h1 class="section-title">{{ $shopHeaderData['title'] ?? '' }}</h1>
                    </div>
                @endif

                <div class="shop-topbar">
                    <div class="shop-search">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.03 4.11l3.43 3.43a.75.75 0 1 1-1.06 1.06l-3.43-3.43A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                        </svg>
                        <input type="text" placeholder="Search products..." class="shop-search-input">
                        <button class="shop-search-btn">Search</button>
                    </div>
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
                        <form class="shop-filter-card" method="GET" action="{{ route('shop') }}">
                            <h3 class="text-lg font-semibold">Filters</h3>
                            <div class="mt-6 space-y-6 text-sm text-slate-600">
                                <div>
                                    <div class="flex items-center justify-between text-sm font-semibold text-slate-700">
                                        <span>Categories</span>
                                        <span>▾</span>
                                    </div>
                                    <ul class="mt-3 space-y-2">
                                        <li class="flex items-center gap-2">
                                            <input type="radio"
                                                   name="category"
                                                   value=""
                                                   class="h-4 w-4 rounded border-slate-300"
                                                   @checked(empty($selectedCategory))>
                                            <span>All categories</span>
                                        </li>
                                        @forelse ($categories as $category)
                                            <li class="flex items-center gap-2">
                                                <input type="radio"
                                                       name="category"
                                                       value="{{ $category->id }}"
                                                       class="h-4 w-4 rounded border-slate-300"
                                                       @checked((string) ($selectedCategory->id ?? '') === (string) $category->id)>
                                                <span>{{ $category->name }}</span>
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
                                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300" checked>
                                            <span>Highest</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300">
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
                                        <input type="range" class="w-full accent-red-500" min="0" max="100" value="55">
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
                                               value="{{ old('min_price', $minPrice ?? '') }}"
                                               placeholder="Minimum (KSh)"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="number"
                                               name="max_price"
                                               min="0"
                                               value="{{ old('max_price', $maxPrice ?? '') }}"
                                               placeholder="Maximum (KSh)"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
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
                                @if (!empty($selectedCategory))
                                    <span class="shop-chip">Category: {{ $selectedCategory->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <span>Short by :</span>
                                <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <option>Popular</option>
                                    <option>Newest</option>
                                    <option>Price: Low to High</option>
                                </select>
                            </div>
                        </div>

                        <div class="shop-grid">
                            @php
                                $shopProducts = $products->count() ? $products : $fallbackProducts;
                            @endphp
                            @foreach ($shopProducts as $product)
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
                            @endforeach
                        </div>
                    </section>
                </div>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
