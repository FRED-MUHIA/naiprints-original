<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>{{ $product->name }} | Nai Prints</title>
        <meta name="description" content="{{ $product->meta_description ?? $product->description ?? 'Shop premium custom prints.' }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            @php
                $name = $product->name;
                $basePrice = (float) $product->price;
                $discountType = $product->discount_type ?? 'none';
                $discountValue = (float) ($product->discount_value ?? 0);
                $hasDiscount = $discountType !== 'none' && $discountValue > 0;
                $finalPrice = $basePrice;
                $discountPercent = 0;

                if ($hasDiscount) {
                    if ($discountType === 'percent') {
                        $finalPrice = $basePrice * (1 - $discountValue / 100);
                        $discountPercent = (int) round($discountValue);
                    } elseif ($discountType === 'amount') {
                        $finalPrice = max(0, $basePrice - $discountValue);
                        $discountPercent = $basePrice > 0 ? (int) round(($discountValue / $basePrice) * 100) : 0;
                    }
                }

                $imageUrl = $product->image_url
                    ?? ($product->images->first() ? \Illuminate\Support\Facades\Storage::url($product->images->first()->image_path) : null);
                $galleryImages = collect();
                if ($imageUrl) {
                    $galleryImages->push($imageUrl);
                }
                foreach ($product->alternateImages as $image) {
                    $url = \Illuminate\Support\Facades\Storage::url($image->image_path);
                    if ($url && !$galleryImages->contains($url)) {
                        $galleryImages->push($url);
                    }
                }
                foreach ($product->galleryImages as $image) {
                    $url = \Illuminate\Support\Facades\Storage::url($image->image_path);
                    if ($url && !$galleryImages->contains($url)) {
                        $galleryImages->push($url);
                    }
                }
                $productId = $product->id;
                $productUrl = route('products.show', ['product' => ($product->slug ?: $product->id)]);
                $categoryNames = $product->categories->pluck('name')->filter()->values();
                if ($categoryNames->isEmpty() && $product->category?->name) {
                    $categoryNames = collect([$product->category->name]);
                }
                $categoryName = $categoryNames->first() ?? 'T-shirt';
                $stockLabel = $product->stock ? 'In stock' : 'Made to order';
                $soldCount = rand(5, 30);
                $viewerCount = rand(12, 45);
                $sku = $product->sku ?? 'N/A';
                $tagList = $product->tags->pluck('name')->filter();
                $colorOptions = collect($product->color_options ?? []);
                if ($colorOptions->isEmpty()) {
                    $colorAttribute = $product->attributes->first(function ($attribute) {
                        $name = strtolower($attribute->name);
                        $slug = strtolower($attribute->slug ?? '');
                        return $name === 'color' || $slug === 'color' || $name === 'colour' || $slug === 'colour';
                    });
                    if ($colorAttribute && !empty($colorAttribute->pivot?->value)) {
                        $colorOptions = collect(preg_split('/[,\n]+/', $colorAttribute->pivot->value));
                    }
                }
                $colorOptions = $colorOptions
                    ->map(function ($color) {
                        $color = trim((string) $color);
                        $color = preg_replace('/[^#a-zA-Z0-9(),.%\\s-]/', '', $color);
                        return trim($color);
                    })
                    ->filter()
                    ->values();
                $sizeOptions = $product->sizes->sortBy('name')->values();
                if ($sizeOptions->isEmpty()) {
                    $sizeFallback = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
                    $sizeOptions = collect($sizeFallback)->map(function ($size) {
                        return (object) ['name' => $size];
                    });
                }
            @endphp

            <main>
                <div class="product-breadcrumb">
                    <div class="mx-auto max-w-6xl px-4">
                        <nav class="breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ url('/') }}">Home</a>
                            <span class="breadcrumb-sep">&gt;</span>
                            <a href="{{ route('shop') }}">{{ $categoryName }}</a>
                            <span class="breadcrumb-sep">&gt;</span>
                            <span class="breadcrumb-current">{{ $name }}</span>
                        </nav>
                    </div>
                </div>

                <div class="mx-auto max-w-6xl px-4 py-10">
                    <section class="product-layout">
                        <div class="product-media-panel">
                            <div class="product-media-gallery" data-product-gallery>
                                <div class="product-thumbs">
                                    @forelse ($galleryImages as $thumb)
                                        <button class="product-thumb @if ($loop->first) is-active @endif" type="button" aria-label="View image {{ $loop->iteration }}" data-gallery-thumb data-gallery-src="{{ $thumb }}">
                                            <img src="{{ $thumb }}" alt="{{ $name }} thumbnail {{ $loop->iteration }}">
                                        </button>
                                    @empty
                                        <div class="product-thumb-placeholder">
                                            <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="product-main">
                                    <div class="image-wrapper">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $name }}" data-gallery-main>
                                        @else
                                            <div class="image-placeholder">
                                                <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <button class="media-heart"
                                                type="button"
                                                aria-label="Add to wishlist"
                                                data-wishlist-toggle
                                                data-product-id="{{ $productId }}"
                                                data-product-name="{{ $name }}"
                                                data-product-price="{{ $finalPrice }}"
                                                data-product-image="{{ $imageUrl }}"
                                                data-product-url="{{ $productUrl }}">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 20.5s-7-4.35-9.33-8.04A5.5 5.5 0 0 1 12 5.2a5.5 5.5 0 0 1 9.33 7.26C19 16.15 12 20.5 12 20.5Z" fill="none" stroke="currentColor" stroke-width="1.5" />
                                            </svg>
                                        </button>
                                        <button class="media-arrow media-arrow-left" type="button" aria-label="Previous image" data-gallery-prev>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M14 7l-5 5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        <button class="media-arrow media-arrow-right" type="button" aria-label="Next image" data-gallery-next>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M10 7l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="product-details" data-product-scope>
                            <p class="category-line">Category: <a href="{{ route('shop') }}" class="category-link">{{ $categoryName }}</a></p>
                            @php
                                $safeName = e((string) $name);
                                $flameName = preg_replace('/\\bT[-\\s]?shirt\\b/i', '<span class=\"flame-text\">$0</span><span class=\"flame-emoji\" aria-hidden=\"true\">🔥</span>', $safeName);
                            @endphp
                            <h1 class="product-title">{!! $flameName !!}</h1>

                            <div class="badges">
                                <span class="status-badge status-badge-outline">{{ $stockLabel }}</span>
                                @if ($hasDiscount)
                                    <span class="status-badge status-badge-sale">Sale</span>
                                @endif
                            </div>

                            <div class="price-row">
                                @if ($hasDiscount)
                                    <span class="old-price">KSh {{ number_format($basePrice, 2) }}</span>
                                @endif
                                <span class="new-price">KSh {{ number_format($finalPrice, 2) }}</span>
                                @if ($hasDiscount)
                                    <span class="discount-pill">{{ $discountPercent }}% OFF</span>
                                @endif
                            </div>

                            <div class="sales-info">
                                <p class="sold"><span class="heartbeat-emoji" aria-hidden="true">🔥</span>Hot {{ $soldCount }} sold in last 12 hours</p>
                                <div class="viewers">
                                    <span class="viewer-count">{{ $viewerCount }}</span>
                                    <span>People are viewing this right now</span>
                                </div>
                            </div>

                            @if ($colorOptions->isNotEmpty() || $sizeOptions->isNotEmpty())
                                <div class="product-options">
                                    @if ($colorOptions->isNotEmpty())
                                        <div class="product-option">
                                            <h4>Color</h4>
                                            <div class="color-swatches" data-option-group="color">
                                                @foreach ($colorOptions as $color)
                                                    <button class="color-swatch @if ($loop->first) is-active @endif"
                                                            type="button"
                                                            style="--swatch-color: {{ $color }};"
                                                            title="{{ $color }}"
                                                            data-option-item
                                                            data-option-value="{{ $color }}">
                                                        <span class="sr-only">{{ $color }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if ($sizeOptions->isNotEmpty())
                                        <div class="product-option">
                                            <h4>Size</h4>
                                            <div class="size-options" data-option-group="size">
                                                @foreach ($sizeOptions as $size)
                                                    <button class="size-pill @if ($loop->first) is-active @endif"
                                                            type="button"
                                                            data-option-item
                                                            data-option-value="{{ $size->name }}">{{ $size->name }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="purchase" data-product-panel>
                                <div class="quantity" data-qty-control>
                                    <button type="button" class="qty-btn" data-qty-decrease aria-label="Decrease quantity">-</button>
                                    <input class="qty-input" type="text" value="1" data-qty-input readonly>
                                    <button type="button" class="qty-btn" data-qty-increase aria-label="Increase quantity">+</button>
                                </div>

                                <button class="add-to-cart" type="button"
                                        data-add-to-cart
                                        data-product-id="{{ $productId }}"
                                        data-product-name="{{ $name }}"
                                        data-product-price="{{ $finalPrice }}"
                                        data-product-image="{{ $imageUrl }}"
                                        data-product-url="{{ $productUrl }}">
                                    Add to cart - KSh {{ number_format($finalPrice, 2) }}
                                </button>

                                <div class="icon-actions">
                                    <button class="icon-btn"
                                            type="button"
                                            aria-label="Add to wishlist"
                                            data-wishlist-toggle
                                            data-product-id="{{ $productId }}"
                                            data-product-name="{{ $name }}"
                                            data-product-price="{{ $finalPrice }}"
                                            data-product-image="{{ $imageUrl }}"
                                            data-product-url="{{ $productUrl }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20.5s-7-4.35-9.33-8.04A5.5 5.5 0 0 1 12 5.2a5.5 5.5 0 0 1 9.33 7.26C19 16.15 12 20.5 12 20.5Z" fill="none" stroke="currentColor" stroke-width="1.5" />
                                        </svg>
                                    </button>
                                    <button class="icon-btn" type="button" aria-label="Compare">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M7 4v16m10-12v12M4 8h6m4-4h6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="meta">
                                <div class="share">
                                    <span>Share</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M18 8a3 3 0 1 0-2.83-4H15a3 3 0 0 0 0 6h.17A3 3 0 0 0 18 8Zm-12 8a3 3 0 1 0 2.83 4H9a3 3 0 0 0 0-6H8.83A3 3 0 0 0 6 16Zm12 0a3 3 0 1 0 2.83 4H21a3 3 0 0 0 0-6h-.17A3 3 0 0 0 18 16ZM8.9 12.5l6.2-3.5m-6.2 3.5l6.2 3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <p><strong>Categories:</strong> {{ $categoryNames->isNotEmpty() ? $categoryNames->join(', ') : $categoryName }}</p>
                                <p><strong>Tag:</strong> {{ $tagList->isNotEmpty() ? $tagList->join(', ') : 'Clothing Black' }}</p>
                                <p><strong>SKU:</strong> {{ $sku }}</p>
                            </div>

                            <div class="delivery">
                                <div class="info-box">
                                    <div class="info-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 7h11v8H3V7Zm11 3h4l3 3v2h-7v-5Zm-8 8a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <p>
                                        Estimate delivery times:<br>
                                        <strong>1-3 days</strong> (International),<br>
                                        <strong>3-6 days</strong> (Kenya).
                                    </p>
                                </div>
                                <div class="info-box">
                                    <div class="info-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 5a7 7 0 1 1-6.1 10.4M4 4v6h6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <p>
                                        Return within <strong>30 days</strong> of purchase.<br>
                                        Duties &amp; taxes are non-refundable.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="product-description">
                        <h2>Product Description</h2>
                        <p>
                            The Better Days T-Shirt is a premium black cotton tee designed for comfort, durability, and everyday style.
                            Featuring the bold "Better Days Ahead" graphic print with vibrant smiley accents, this shirt is perfect for casual wear,
                            street fashion, and statement styling.
                        </p>
                        @php
                            $colorLabelList = $colorOptions->isNotEmpty() ? $colorOptions : collect(['Black']);
                            $sizeLabelList = $sizeOptions->isNotEmpty()
                                ? $sizeOptions->pluck('name')
                                : collect(['S', 'M', 'L', 'XL', 'XXL', '3XL']);
                        @endphp
                        <ul>
                            <li>100% high-quality cotton</li>
                            <li>Soft breathable fabric</li>
                            <li>Unisex oversized fit</li>
                            <li>Durable print finish</li>
                            <li>Machine washable</li>
                            <li>Color: {{ $colorLabelList->join(', ') }}</li>
                            <li>Sizes available: {{ $sizeLabelList->join(', ') }}</li>
                        </ul>
                    </section>

                    @if (!empty($relatedProducts) && $relatedProducts->count())
                        <section class="related-products">
                            <h2 class="related-title">Related products</h2>
                            <div class="related-carousel" data-related-carousel>
                                <button class="carousel-btn" type="button" aria-label="Previous products" data-related-prev>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.78 15.78a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="related-track" data-related-track>
                                @foreach ($relatedProducts as $related)
                                    @php
                                        $relatedName = $related->name;
                                        $relatedPrice = (float) $related->price;
                                        $relatedDiscountType = $related->discount_type ?? 'none';
                                        $relatedDiscountValue = (float) ($related->discount_value ?? 0);
                                        $relatedHasDiscount = $relatedDiscountType !== 'none' && $relatedDiscountValue > 0;
                                        $relatedFinal = $relatedPrice;
                                        $relatedBadge = '';
                                        if ($relatedHasDiscount) {
                                            if ($relatedDiscountType === 'percent') {
                                                $relatedFinal = $relatedPrice * (1 - $relatedDiscountValue / 100);
                                                $relatedBadge = '-' . rtrim(rtrim(number_format($relatedDiscountValue, 0), '0'), '.') . '%';
                                            } elseif ($relatedDiscountType === 'amount') {
                                                $relatedFinal = max(0, $relatedPrice - $relatedDiscountValue);
                                                $relatedPercent = $relatedPrice > 0 ? (int) round(($relatedDiscountValue / $relatedPrice) * 100) : 0;
                                                $relatedBadge = $relatedPercent > 0 ? '-' . $relatedPercent . '%' : '-KSh ' . number_format($relatedDiscountValue, 0);
                                            }
                                        }
                                        $relatedIsNew = $related->created_at ? $related->created_at->gt(now()->subDays(21)) : false;
                                        $relatedBadge = $relatedBadge ?: ($relatedIsNew ? 'NEW' : '');
                                        $relatedBadgeClass = $relatedHasDiscount ? 'product-badge-sale' : ($relatedIsNew ? 'product-badge-new' : '');
                                        $relatedImage = $related->image_url
                                            ?? ($related->images->first()
                                                ? \Illuminate\Support\Facades\Storage::url($related->images->first()->image_path)
                                                : null);
                                        $relatedAlt = $related->alternateImages->first() ?: $related->galleryImages->first();
                                        $relatedHover = $relatedAlt
                                            ? \Illuminate\Support\Facades\Storage::url($relatedAlt->image_path)
                                            : null;
                                        $relatedUrl = route('products.show', ['product' => ($related->slug ?: $related->id)]);
                                        $safeRelated = e((string) $relatedName);
                                        $relatedFlame = preg_replace('/\\bT[-\\s]?shirt\\b/i', '<span class=\"flame-text\">$0</span><span class=\"flame-emoji\" aria-hidden=\"true\">🔥</span>', $safeRelated);
                                    @endphp
                                    <div class="related-slide" data-related-item>
                                        <article class="product-card product-card-compact" data-product-panel>
                                        <div class="product-media">
                                            <a href="{{ $relatedUrl }}" class="product-media-link" aria-label="View {{ $relatedName }}"></a>
                                            @if ($relatedImage)
                                                <img src="{{ $relatedImage }}" alt="{{ $relatedName }}" loading="lazy" class="product-media-img is-primary">
                                                @if ($relatedHover)
                                                    <img src="{{ $relatedHover }}" alt="{{ $relatedName }} alternate" loading="lazy" class="product-media-img is-secondary">
                                                @endif
                                            @else
                                                <div class="product-media-placeholder">
                                                    <span>{{ strtoupper(substr($relatedName, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                            @if (!empty($relatedBadge))
                                                <span class="product-badge {{ $relatedBadgeClass }}">{{ $relatedBadge }}</span>
                                            @endif
                                        </div>
                                        <div class="product-quick">
                                            <button class="product-quick-add" type="button"
                                                    data-add-to-cart
                                                    data-product-id="{{ $related->id }}"
                                                    data-product-name="{{ $relatedName }}"
                                                    data-product-price="{{ $relatedFinal }}"
                                                    data-product-image="{{ $relatedImage }}"
                                                    data-product-url="{{ $relatedUrl }}">
                                                Quick Add
                                            </button>
                                        </div>
                                        <div class="mt-4 space-y-2 product-card-body">
                                            <div>
                                                <h3 class="product-card-title"><span class="heartbeat-emoji" aria-hidden="true">🔥</span>{!! $relatedFlame !!}</h3>
                                            </div>
                                            <div class="product-card-price">
                                                <span class="product-card-price-current">KSh {{ number_format($relatedFinal, 0) }}</span>
                                                @if ($relatedHasDiscount)
                                                    <span class="product-card-price-old">KSh {{ number_format($relatedPrice, 0) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        </article>
                                    </div>
                                @endforeach
                                </div>
                                <button class="carousel-btn" type="button" aria-label="Next products" data-related-next>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06-1.06L11.69 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </section>
                    @endif
                </div>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.__naiProductUiFallbackBound) {
                    return;
                }
                window.__naiProductUiFallbackBound = true;

                const panel = document.querySelector('[data-product-panel]');

                document.querySelectorAll('[data-option-group]').forEach((group) => {
                    const items = Array.from(group.querySelectorAll('[data-option-item]'));
                    if (items.length === 0) {
                        return;
                    }

                    const groupType = (group.dataset.optionGroup || 'option').toLowerCase();
                    const readValue = (item) => item.dataset.optionValue || item.textContent?.trim() || '';

                    const applySelection = (selectedItem) => {
                        items.forEach((item) => item.classList.remove('is-active'));
                        selectedItem.classList.add('is-active');

                        if (!panel) {
                            return;
                        }

                        const value = readValue(selectedItem);
                        if (!value) {
                            return;
                        }

                        if (groupType === 'color') {
                            panel.dataset.selectedColor = value;
                        } else if (groupType === 'size') {
                            panel.dataset.selectedSize = value;
                        }
                    };

                    const preset = items.find((item) => item.classList.contains('is-active')) || items[0];
                    if (preset) {
                        applySelection(preset);
                    }

                    items.forEach((item) => {
                        item.addEventListener('click', (event) => {
                            event.preventDefault();
                            applySelection(item);
                        });
                    });
                });

                const gallery = document.querySelector('[data-product-gallery]');
                if (gallery) {
                    const mainImage = gallery.querySelector('[data-gallery-main]');
                    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
                    const prev = gallery.querySelector('[data-gallery-prev]');
                    const next = gallery.querySelector('[data-gallery-next]');

                    if (mainImage && thumbs.length > 0) {
                        let activeIndex = Math.max(
                            0,
                            thumbs.findIndex((thumb) => thumb.classList.contains('is-active'))
                        );

                        const setActive = (index) => {
                            const clamped = ((index % thumbs.length) + thumbs.length) % thumbs.length;
                            const target = thumbs[clamped];
                            const src = target?.dataset.gallerySrc;
                            if (!src) {
                                return;
                            }

                            activeIndex = clamped;
                            mainImage.setAttribute('src', src);
                            thumbs.forEach((thumb) => thumb.classList.remove('is-active'));
                            target.classList.add('is-active');
                        };

                        thumbs.forEach((thumb, index) => {
                            thumb.addEventListener('click', (event) => {
                                event.preventDefault();
                                setActive(index);
                            });
                        });

                        prev?.addEventListener('click', (event) => {
                            event.preventDefault();
                            setActive(activeIndex - 1);
                        });

                        next?.addEventListener('click', (event) => {
                            event.preventDefault();
                            setActive(activeIndex + 1);
                        });

                        setActive(activeIndex);
                    }
                }

                const qtyControl = panel?.querySelector('[data-qty-control]');
                const qtyInput = qtyControl?.querySelector('[data-qty-input]');
                const qtyDecrease = qtyControl?.querySelector('[data-qty-decrease]');
                const qtyIncrease = qtyControl?.querySelector('[data-qty-increase]');

                if (qtyInput) {
                    const setQty = (value) => {
                        const next = Number.isNaN(value) ? 1 : Math.max(1, value);
                        qtyInput.value = String(next);
                    };

                    qtyDecrease?.addEventListener('click', (event) => {
                        event.preventDefault();
                        const current = Number.parseInt(qtyInput.value || '1', 10);
                        setQty((Number.isNaN(current) ? 1 : current) - 1);
                    });

                    qtyIncrease?.addEventListener('click', (event) => {
                        event.preventDefault();
                        const current = Number.parseInt(qtyInput.value || '1', 10);
                        setQty((Number.isNaN(current) ? 1 : current) + 1);
                    });
                }

                window.setTimeout(() => {
                    const addButton = document.querySelector('.purchase [data-add-to-cart]');
                    if (!addButton || addButton.dataset.cartBound === '1' || addButton.dataset.cartFallbackBound === '1') {
                        return;
                    }

                    addButton.dataset.cartFallbackBound = '1';
                    const originalLabel = addButton.textContent;
                    const cartKey = 'nai_cart';

                    const readCart = () => {
                        try {
                            const stored = localStorage.getItem(cartKey);
                            return stored ? JSON.parse(stored) : [];
                        } catch (error) {
                            return [];
                        }
                    };

                    const writeCart = (items) => {
                        try {
                            localStorage.setItem(cartKey, JSON.stringify(items));
                        } catch (error) {
                            // Ignore storage errors.
                        }
                    };

                    const updateCartBadges = () => {
                        const count = readCart().reduce((total, item) => total + (Number(item.qty) || 0), 0);
                        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
                            badge.textContent = String(count);
                            badge.classList.toggle('hidden', count === 0);
                            badge.classList.toggle('is-green', count > 0);
                        });
                    };

                    addButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        const { productId, productName, productPrice, productImage, productUrl } = addButton.dataset;
                        if (!productId) {
                            return;
                        }

                        const selectedColor = panel?.dataset.selectedColor || '';
                        const selectedSize = panel?.dataset.selectedSize || '';
                        const qty = Math.max(1, Number.parseInt(qtyInput?.value || '1', 10) || 1);
                        const variantId =
                            selectedColor || selectedSize ? `${productId}::${selectedColor}::${selectedSize}` : productId;

                        const items = readCart();
                        const existing = items.find((item) => item.id === variantId);
                        if (existing) {
                            existing.qty = (Number(existing.qty) || 0) + qty;
                        } else {
                            items.push({
                                id: variantId,
                                baseId: productId,
                                name: productName || 'Product',
                                price: Number.parseFloat(productPrice || '0') || 0,
                                image: productImage || '',
                                url: productUrl || '',
                                color: selectedColor,
                                size: selectedSize,
                                qty,
                            });
                        }

                        writeCart(items);
                        updateCartBadges();
                        addButton.classList.add('is-added');
                        addButton.textContent = 'Added to cart';
                        window.setTimeout(() => {
                            addButton.classList.remove('is-added');
                            addButton.textContent = originalLabel;
                        }, 1200);
                    });
                }, 0);
            });
        </script>
    </body>
</html>
