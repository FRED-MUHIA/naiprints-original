<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => 'Maasai-Inspired Personalized Gifts in Nairobi | Nai Prints',
            'description' => 'Shop Maasai-inspired personalized gifts, shuka-trim apparel, baby keepsakes, and corporate merch made by Nai Prints in Nairobi.',
            'canonical' => route('maasai'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main>
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $section = collect($sections)->firstWhere('type', 'maasai_page');
                    $defaults = \App\Support\PageDefaults::maasai()['sections'][0]['data'] ?? [];
                    $data = $section['data'] ?? [];
                    $slides = $data['slides'] ?? ($defaults['slides'] ?? []);
                    $categories = $data['categories'] ?? ($defaults['categories'] ?? []);
                    $howItWorks = $data['how_it_works'] ?? ($defaults['how_it_works'] ?? []);
                    $howItWorksSteps = $howItWorks['steps'] ?? [];
                    $productCollection = $data['product_collection'] ?? ($defaults['product_collection'] ?? []);
                    $fallbackProducts = $data['products'] ?? ($defaults['products'] ?? []);
                    $testimonials = $data['testimonials'] ?? ($defaults['testimonials'] ?? []);
                    $testimonialItems = collect($testimonials['items'] ?? [])
                        ->filter(fn ($testimonial) => is_array($testimonial) && trim((string) ($testimonial['name'] ?? '')) !== '' && trim((string) ($testimonial['body'] ?? '')) !== '')
                        ->values();
                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);
                    $realProducts = $maasaiProducts ?? collect();
                    $displayProducts = $realProducts->isNotEmpty() ? $realProducts : collect($fallbackProducts);
                @endphp

                <section class="maasai-hero" data-maasai-slider>
                    <div class="maasai-slider">
                        @foreach ($slides as $index => $slide)
                            @php
                                $defaultSlide = $defaults['slides'][$index] ?? [];
                                $overlayImage = $slide['overlay_image'] ?? ($defaultSlide['overlay_image'] ?? null);
                                $overlayAlt = $slide['overlay_alt'] ?? ($defaultSlide['overlay_alt'] ?? ($slide['alt'] ?? ''));
                            @endphp
                            <article class="maasai-slide {{ $index === 0 ? 'is-active' : '' }}" data-maasai-slide>
                                <img src="{{ $slide['image'] ?? '' }}" alt="{{ $slide['alt'] ?? '' }}" class="maasai-slide-image">
                                <div class="maasai-slide-overlay"></div>
                                @if (!empty($overlayImage))
                                    <span class="maasai-slide-floating" aria-hidden="{{ empty($overlayAlt) ? 'true' : 'false' }}">
                                        <img src="{{ $overlayImage }}" alt="{{ $overlayAlt }}" class="maasai-slide-floating-image">
                                    </span>
                                @endif
                                <div class="maasai-slide-copy">
                                    @if (!empty($slide['kicker']))
                                        <p class="maasai-kicker">{{ $slide['kicker'] }}</p>
                                    @endif
                                    <h1>{{ $slide['title'] ?? '' }}</h1>
                                    @if (!empty($slide['body']))
                                        <p class="maasai-slide-body">{{ $slide['body'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if (count($slides) > 1)
                        <div class="maasai-slider-controls" aria-label="Maasai collection slides">
                            @foreach ($slides as $index => $slide)
                                <button
                                    class="maasai-slider-dot {{ $index === 0 ? 'is-active' : '' }}"
                                    type="button"
                                    aria-label="Show slide {{ $index + 1 }}"
                                    data-maasai-slide-control
                                    data-slide-index="{{ $index }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </section>
                <div class="maasai-pattern-strip" aria-hidden="true"></div>

                <section class="mx-auto max-w-6xl px-4 py-12">
                    <div class="maasai-section-head">
                        <p class="section-kicker">{{ $howItWorks['kicker'] ?? '' }}</p>
                        <h2 class="section-title">{{ $howItWorks['title'] ?? '' }}</h2>
                    </div>
                    <div class="maasai-steps">
                        @foreach ($howItWorksSteps as $index => $step)
                            <div class="maasai-step">
                                <span class="maasai-step-number">{{ $index + 1 }}</span>
                                <div>
                                    <h3>{{ $step['title'] }}</h3>
                                    <p>{{ $step['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto max-w-6xl px-4 pb-12">
                    <div class="maasai-category-grid">
                        @foreach ($categories as $category)
                            <a href="{{ $category['url'] ?? '#maasai-products' }}" class="maasai-category-card">
                                <img src="{{ $category['image'] ?? '' }}" alt="{{ $category['title'] ?? '' }}">
                                <span class="maasai-category-shade"></span>
                                <span class="maasai-category-copy">
                                    <strong>{{ $category['title'] ?? '' }}</strong>
                                    <small>{{ $category['body'] ?? '' }}</small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section id="maasai-products" class="mx-auto max-w-6xl px-4 pb-14">
                    @php
                        $usdToKesRate = max((float) config('services.currency.usd_to_kes_rate', 130), 0.01);
                    @endphp
                    <div class="maasai-section-head">
                        <p class="section-kicker">{{ $productCollection['kicker'] ?? '' }}</p>
                        <h2 class="section-title">{{ $productCollection['title'] ?? '' }}</h2>
                    </div>
                    <div class="maasai-product-grid">
                        @foreach ($displayProducts as $product)
                            @php
                                $isModel = $product instanceof \App\Models\Product;
                                $name = $isModel ? $product->name : ($product['name'] ?? 'Custom Maasai-inspired gift');
                                $price = (float) ($isModel ? $product->price : ($product['price'] ?? 0));
                                $usdPrice = $price / $usdToKesRate;
                                $productId = $isModel ? (string) $product->getKey() : 'maasai-fallback-' . $loop->iteration;
                                $detailUrl = $isModel ? route('products.show', ['product' => $product->slug ?: $product->getKey()]) : route('contact.show');
                                $imageUrl = $isModel ? ($product->image_url ?? null) : ($product['image'] ?? null);
                                if ($isModel && !$imageUrl) {
                                    $fallback = $product->galleryImages->first()
                                        ?? $product->alternateImages->first()
                                        ?? $product->images->first();
                                    if ($fallback && $fallback->image_path) {
                                        $imageUrl = \Illuminate\Support\Facades\Storage::url($fallback->image_path);
                                    }
                                }
                            @endphp
                            <article class="shop-card maasai-product-card">
                                <div class="shop-card-media">
                                    <a href="{{ $detailUrl }}" class="block h-full w-full">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="shop-card-placeholder">
                                                <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                <div class="maasai-product-content mt-4 space-y-3">
                                    <div class="maasai-product-meta flex items-center justify-between text-xs text-slate-500">
                                        <span>Nai Prints</span>
                                        <span>Maasai-inspired</span>
                                    </div>
                                    <div class="maasai-product-info flex items-start justify-between gap-3">
                                        <div class="maasai-product-copy">
                                            <a href="{{ $detailUrl }}" class="maasai-product-name text-sm font-semibold text-slate-700 hover:text-brand">
                                                {{ $name }}
                                            </a>
                                            <div class="mt-1">
                                                <p class="maasai-product-price text-lg font-semibold text-slate-950">KSh {{ number_format($price, 0) }}</p>
                                                <p class="maasai-product-usd text-xs font-semibold text-slate-500">USD {{ number_format($usdPrice, 2) }}</p>
                                            </div>
                                        </div>
                                        <div class="maasai-product-actions flex items-center gap-2">
                                            <button class="add-button wishlist-button" type="button"
                                                    aria-label="Add to wishlist"
                                                    data-wishlist-toggle
                                                    data-product-id="{{ $productId }}"
                                                    data-product-name="{{ $name }}"
                                                    data-product-price="{{ $price }}"
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
                                                    data-product-price="{{ $price }}"
                                                    data-product-image="{{ $imageUrl }}"
                                                    data-product-url="{{ $detailUrl }}">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M10 4.5a.75.75 0 0 1 .75.75v4h4a.75.75 0 0 1 0 1.5h-4v4a.75.75 0 0 1-1.5 0v-4h-4a.75.75 0 0 1 0-1.5h4v-4A.75.75 0 0 1 10 4.5Z" />
                                                </svg>
                                                <span class="sr-only">Add {{ $name }} to cart</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-8 text-center">
                        <a href="{{ $productCollection['view_all_url'] ?? route('shop') }}" class="btn-primary">{{ $productCollection['view_all_label'] ?? 'View All' }}</a>
                    </div>
                </section>

                <section class="maasai-cta">
                    <div class="mx-auto max-w-6xl px-4">
                        <div class="cta-card maasai-cta-card">
                            <div>
                                <p class="cta-kicker">{{ $cta['kicker'] ?? '' }}</p>
                                <h2 class="mt-4 text-3xl font-semibold text-white">{{ $cta['title'] ?? '' }}</h2>
                                <p class="mt-3 text-sm text-white/80">
                                    {{ $cta['body'] ?? '' }}
                                </p>
                            </div>
                            <div class="mt-6 flex flex-wrap gap-3">
                                @if (!empty($cta['primary']['label']))
                                    <a href="{{ $cta['primary']['url'] ?? route('contact.show') }}" class="btn-primary">{{ $cta['primary']['label'] }}</a>
                                @endif
                                @if (!empty($cta['secondary']['label']))
                                    <a href="{{ $cta['secondary']['url'] ?? route('corporate') }}" class="btn-secondary">{{ $cta['secondary']['label'] }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                @if ($testimonialItems->isNotEmpty())
                    <section class="maasai-testimonials" aria-labelledby="maasai-testimonials-title">
                        <div class="mx-auto max-w-6xl px-4">
                            <div class="maasai-testimonials-head">
                                <div>
                                    @if (!empty($testimonials['kicker']))
                                        <p class="section-kicker">{{ $testimonials['kicker'] }}</p>
                                    @endif
                                    <h2 id="maasai-testimonials-title" class="section-title">{{ $testimonials['title'] ?? 'Customer reviews' }}</h2>
                                </div>
                            </div>
                            <div class="maasai-testimonials-carousel" data-category-carousel>
                                <button class="carousel-btn maasai-testimonial-arrow" type="button" aria-label="Previous testimonials" data-carousel-prev>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.78 15.78a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="maasai-testimonials-track" data-carousel-track>
                                    @foreach ($testimonialItems as $testimonial)
                                        @php
                                            $name = trim((string) ($testimonial['name'] ?? 'Customer'));
                                            $initials = collect(explode(' ', $name))
                                                ->filter()
                                                ->take(2)
                                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                                ->join('');
                                            $rating = max(1, min(5, (int) ($testimonial['rating'] ?? 5)));
                                            $source = trim((string) ($testimonial['source'] ?? ''));
                                        @endphp
                                        <article class="maasai-testimonial-card" data-carousel-item>
                                            <div class="maasai-testimonial-top">
                                                <div class="maasai-testimonial-avatar">
                                                    @if (!empty($testimonial['avatar']))
                                                        <img src="{{ $testimonial['avatar'] }}" alt="{{ $name }}">
                                                    @else
                                                        <span>{{ $initials ?: 'NP' }}</span>
                                                    @endif
                                                </div>
                                                <div class="maasai-testimonial-person">
                                                    <h3>{{ $name }}</h3>
                                                    @if (!empty($testimonial['date']))
                                                        <p>{{ $testimonial['date'] }}</p>
                                                    @endif
                                                </div>
                                                @if ($source !== '')
                                                    <span class="maasai-testimonial-source" aria-label="{{ $source }}">{{ strtoupper(substr($source, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div class="maasai-testimonial-stars" aria-label="{{ $rating }} out of 5 stars">
                                                @for ($star = 1; $star <= 5; $star++)
                                                    <span class="{{ $star <= $rating ? '' : 'is-muted' }}" aria-hidden="true">&#9733;</span>
                                                @endfor
                                            </div>
                                            <p class="maasai-testimonial-text">{{ $testimonial['body'] }}</p>
                                        </article>
                                    @endforeach
                                </div>
                                <button class="carousel-btn maasai-testimonial-arrow" type="button" aria-label="Next testimonials" data-carousel-next>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06-1.06L11.69 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </section>
                @endif
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
