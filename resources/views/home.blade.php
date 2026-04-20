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
        <title>Nai Prints | T-Shirt & Garment Printing</title>
        <meta name="description" content="Nai Prints creates bold, custom t-shirts, hoodies, and uniforms for every vibe. Design online or order in bulk.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main>
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $ctaRendered = false;
                    $hasGiftFinderSection = collect($sections)->contains(function ($section) {
                        return ($section['type'] ?? '') === 'gift_finder';
                    });
                    $normalizeGiftFinderKey = static function ($value) {
                        return \Illuminate\Support\Str::of((string) $value)
                            ->lower()
                            ->replaceMatches('/[^a-z0-9]+/', '')
                            ->toString();
                    };
                    $giftFinderCategoryEntries = collect($giftFinderCategories ?? [])
                        ->map(function ($category) use ($normalizeGiftFinderKey) {
                            $id = (int) data_get($category, 'id', 0);
                            $name = (string) data_get($category, 'name', '');
                            $slug = (string) data_get($category, 'slug', '');

                            $aliases = array_values(array_unique(array_filter([
                                $normalizeGiftFinderKey($name),
                                $normalizeGiftFinderKey($slug),
                                $normalizeGiftFinderKey(str_replace('-', ' ', $slug)),
                            ])));

                            return [
                                'id' => $id,
                                'aliases' => $aliases,
                            ];
                        })
                        ->filter(fn ($category) => ($category['id'] ?? 0) > 0)
                        ->values();
                    $resolveGiftFinderOptionUrl = static function ($optionLabel, $fallbackUrl = '/shop', $preferredCategoryId = null) use ($giftFinderCategoryEntries, $normalizeGiftFinderKey) {
                        $fallback = is_string($fallbackUrl) && trim($fallbackUrl) !== ''
                            ? trim($fallbackUrl)
                            : '/shop';

                        if (is_numeric($preferredCategoryId)) {
                            return route('shop', ['category' => (int) $preferredCategoryId]);
                        }

                        $needle = $normalizeGiftFinderKey($optionLabel);
                        if ($needle === '') {
                            return $fallback;
                        }

                        $match = $giftFinderCategoryEntries->first(function ($category) use ($needle) {
                            $aliases = $category['aliases'] ?? [];
                            if (in_array($needle, $aliases, true)) {
                                return true;
                            }

                            foreach ($aliases as $alias) {
                                if ($alias !== '' && (str_contains($needle, $alias) || str_contains($alias, $needle))) {
                                    return true;
                                }
                            }

                            return false;
                        });

                        return $match
                            ? route('shop', ['category' => (int) $match['id']])
                            : $fallback;
                    };
                    $resolveContactCtaUrl = static function ($label, $url = '#') {
                        $normalizedLabel = strtolower(trim((string) $label));
                        $resolvedUrl = trim((string) $url);
                        if (
                            in_array($normalizedLabel, ['request a quote', 'start your order'], true)
                            && ($resolvedUrl === '' || $resolvedUrl === '#' || $resolvedUrl === '/shop')
                        ) {
                            return route('contact.show');
                        }

                        return $resolvedUrl !== '' ? $resolvedUrl : '#';
                    };
                    $giftProducts = [];
                    $giftFinderSource = !empty($giftFinderProducts) && $giftFinderProducts->count()
                        ? $giftFinderProducts
                        : $featuredProducts;
                    if (!empty($giftFinderSource) && $giftFinderSource->count()) {
                        $giftProducts = $giftFinderSource->map(function ($product) {
                            $imageUrl = $product->image_url;
                            if (!$imageUrl) {
                                $fallback = $product->galleryImages->first()
                                    ?? $product->alternateImages->first()
                                    ?? $product->images->first();
                                if ($fallback && $fallback->image_path) {
                                    $imageUrl = \Illuminate\Support\Facades\Storage::url($fallback->image_path);
                                }
                            }
                            $brand = $product->brand?->name;
                            $author = $brand ? "by {$brand}" : 'by Nai Prints';
                            $price = is_numeric($product->price ?? null)
                                ? 'KSh ' . number_format((float) $product->price, 0)
                                : 'KSh 0';

                            return [
                                'title' => $product->name ?? '',
                                'author' => $author,
                                'price' => $price,
                                'image' => $imageUrl ?: '',
                                'url' => route('products.show', ['product' => ($product->slug ?: $product->id)]),
                            ];
                        })->filter(fn ($item) => !empty($item['title']))->values()->all();
                    } elseif (!empty($products)) {
                        $giftProducts = collect($products)->map(function ($product) {
                            return [
                                'title' => $product['title'] ?? '',
                                'author' => 'by Nai Prints',
                                'price' => $product['price'] ?? 'KSh 0',
                                'image' => '',
                                'url' => '/shop',
                            ];
                        })->filter(fn ($item) => !empty($item['title']))->values()->all();
                    }
                @endphp

                @for ($i = 0; $i < count($sections); $i++)
                    @php
                        $section = $sections[$i] ?? [];
                        $type = $section['type'] ?? '';
                    @endphp

                    @if ($type === 'hero')
                        @php
                            $data = $section['data'] ?? [];
                            $heroPrimaryLabel = trim((string) ($data['primary']['label'] ?? ''));
                            $heroPrimaryUrl = $resolveContactCtaUrl($heroPrimaryLabel, $data['primary']['url'] ?? '#');
                            $tilesSection = null;
                            if (isset($sections[$i + 1]) && ($sections[$i + 1]['type'] ?? '') === 'tiles') {
                                $tilesSection = $sections[$i + 1];
                                $i++;
                            }
                            $tiles = $tilesSection['data']['tiles'] ?? [];
                        @endphp
                        <section class="mx-auto grid max-w-6xl gap-4 px-4 pb-10 pt-10 lg:grid-cols-4 lg:gap-6">
                            <div class="hero-card lg:col-span-2">
                                <span class="pill">{{ $data['kicker'] ?? '' }}</span>
                                <h1 class="mt-6 text-4xl font-bold leading-tight text-white md:text-5xl">
                                    {{ $data['title'] ?? '' }} <span class="text-brand">{{ $data['highlight'] ?? '' }}</span>
                                </h1>
                                <p class="mt-4 text-sm text-white/80 md:text-base">
                                    {{ $data['body'] ?? '' }}
                                </p>
                                <div class="mt-8 flex flex-wrap items-center gap-4">
                                    @if ($heroPrimaryLabel !== '')
                                        <a href="{{ $heroPrimaryUrl }}" class="btn-primary">{{ $heroPrimaryLabel }}</a>
                                    @endif
                                    @if (!empty($data['secondary']['label']))
                                        <a href="{{ $data['secondary']['url'] ?? '#' }}" class="btn-secondary">{{ $data['secondary']['label'] }}</a>
                                    @endif
                                </div>
                                @if (!empty($data['stats']))
                                    <div class="mt-10 flex flex-wrap gap-6 text-sm text-white/80">
                                        @foreach ($data['stats'] as $stat)
                                            <div>
                                                <p class="text-xl font-semibold text-white">{{ $stat['value'] ?? '' }}</p>
                                                <p>{{ $stat['label'] ?? '' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if (!empty($tiles))
                                @foreach ($tiles as $tileIndex => $tile)
                                    @php
                                        $tileClass = 'hero-tile';
                                        if (!empty($tile['theme'])) {
                                            $tileClass .= ' ' . $tile['theme'];
                                        }
                                        if ($tileIndex === count($tiles) - 1 && count($tiles) > 2) {
                                            $tileClass .= ' lg:col-span-2';
                                        }
                                        $tileBackground = $tile['background_image'] ?? '';
                                        $tileFlip = $tile['background_image_alt'] ?? '';
                                        $tileVideo = $tile['video_url'] ?? '';
                                        $tileVideoType = '';
                                        if (!empty($tileVideo)) {
                                            $videoPath = parse_url($tileVideo, PHP_URL_PATH) ?? '';
                                            $videoExt = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
                                            if ($videoExt === 'webm') {
                                                $tileVideoType = 'video/webm';
                                            } elseif ($videoExt === 'ogg' || $videoExt === 'ogv') {
                                                $tileVideoType = 'video/ogg';
                                            } elseif ($videoExt === 'mp4') {
                                                $tileVideoType = 'video/mp4';
                                            }
                                        }
                                        $tileStyle = '';
                                        if (!empty($tileBackground) && empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-image';
                                            $tileStyle = "background-image: url('{$tileBackground}');";
                                        }
                                        if (!empty($tileBackground) && !empty($tileFlip) && empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-flip';
                                            // Keep the background image as a fallback for the front face.
                                        }
                                        if (!empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-video';
                                        }
                                    @endphp
                                    <div class="{{ $tileClass }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                        @if (!empty($tileVideo))
                                            <video class="hero-tile-video-el" muted loop playsinline autoplay preload="metadata">
                                                <source src="{{ $tileVideo }}" @if (!empty($tileVideoType)) type="{{ $tileVideoType }}" @endif>
                                            </video>
                                        @endif
                                        @if (!empty($tileBackground) && !empty($tileFlip) && empty($tileVideo))
                                            <div class="hero-tile-flip-inner">
                                                <div class="hero-tile-face hero-tile-front" style="background-image: url('{{ $tileBackground }}');"></div>
                                                <div class="hero-tile-face hero-tile-back" style="background-image: url('{{ $tileFlip }}');"></div>
                                            </div>
                                        @endif
                                        <div class="hero-title">
                                            <p class="text-xs uppercase tracking-[0.3em] text-white/70">{{ $tile['kicker'] ?? '' }}</p>
                                            <h3 class="text-2xl font-semibold text-white">{{ $tile['title'] ?? '' }}</h3>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </section>
                    @elseif ($type === 'tiles')
                        @php
                            $tiles = $section['data']['tiles'] ?? [];
                        @endphp
                        @if (!empty($tiles))
                            <section class="tiles-section mx-auto grid max-w-6xl gap-4 px-4 pb-10 lg:grid-cols-3 lg:gap-6">
                                @foreach ($tiles as $tileIndex => $tile)
                                    @php
                                        $tileBackground = $tile['background_image'] ?? '';
                                        $tileFlip = $tile['background_image_alt'] ?? '';
                                        $tileVideo = $tile['video_url'] ?? '';
                                        if (empty($tileFlip) && $tileIndex === 0 && !empty($tileBackground) && empty($tileVideo)) {
                                            $tileFlip = $tileBackground;
                                        }
                                        $tileVideoType = '';
                                        if (!empty($tileVideo)) {
                                            $videoPath = parse_url($tileVideo, PHP_URL_PATH) ?? '';
                                            $videoExt = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
                                            if ($videoExt === 'webm') {
                                                $tileVideoType = 'video/webm';
                                            } elseif ($videoExt === 'ogg' || $videoExt === 'ogv') {
                                                $tileVideoType = 'video/ogg';
                                            } elseif ($videoExt === 'mp4') {
                                                $tileVideoType = 'video/mp4';
                                            }
                                        }
                                        $tileClass = 'hero-tile ' . ($tile['theme'] ?? '');
                                        $tileStyle = '';
                                        if (!empty($tileBackground) && empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-image';
                                            $tileStyle = "background-image: url('{$tileBackground}');";
                                        }
                                        if (!empty($tileBackground) && !empty($tileFlip) && empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-flip';
                                            // Keep the background image as a fallback for the front face.
                                        }
                                        if (!empty($tileVideo)) {
                                            $tileClass .= ' hero-tile-video';
                                        }
                                    @endphp
                                    <div class="{{ $tileClass }}" data-tile-index="{{ $tileIndex }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                        @if (!empty($tileVideo))
                                            <video class="hero-tile-video-el" muted loop playsinline autoplay preload="metadata">
                                                <source src="{{ $tileVideo }}" @if (!empty($tileVideoType)) type="{{ $tileVideoType }}" @endif>
                                            </video>
                                        @endif
                                        @if (!empty($tileBackground) && !empty($tileFlip) && empty($tileVideo))
                                            <div class="hero-tile-flip-inner">
                                                <div class="hero-tile-face hero-tile-front" style="background-image: url('{{ $tileBackground }}');"></div>
                                                <div class="hero-tile-face hero-tile-back" style="background-image: url('{{ $tileFlip }}');"></div>
                                            </div>
                                        @endif
                                        <div class="hero-title">
                                            <p class="text-xs uppercase tracking-[0.3em] text-white/70">{{ $tile['kicker'] ?? '' }}</p>
                                            <h3 class="text-2xl font-semibold text-white">{{ $tile['title'] ?? '' }}</h3>
                                        </div>
                                    </div>
                                @endforeach
                            </section>
                        @endif
                    @elseif ($type === 'category_carousel')
                        @php
                            $data = $section['data'] ?? [];
                            $items = $data['items'] ?? [];
                            if (empty($items)) {
                                $items = collect($categories ?? [])->map(function ($category) {
                                    return [
                                        'title' => $category['title'] ?? '',
                                        'count' => $category['items'] ?? 0,
                                        'image_url' => '',
                                        'icon' => '',
                                        'url' => '#',
                                        'gradient' => $category['gradient'] ?? 'linear-gradient(135deg, #e5e7eb, #ffffff)',
                                    ];
                                })->all();
                            }
                        @endphp
                        <section class="mx-auto max-w-6xl px-4 pb-8">
                            <div class="flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="section-kicker">{{ $data['kicker'] ?? '' }}</p>
                                    <h2 class="section-title">{{ $data['title'] ?? '' }}</h2>
                                </div>
                                @if (!empty($data['view_all_label']))
                                    <a href="{{ $data['view_all_url'] ?? '#' }}" class="text-sm font-semibold text-slate-700 underline underline-offset-4">
                                        {{ $data['view_all_label'] }}
                                    </a>
                                @endif
                            </div>

                            <div class="category-carousel" data-category-carousel>
                                <button class="carousel-btn" type="button" aria-label="Previous categories" data-carousel-prev>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.78 15.78a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div class="category-slider" data-carousel-track>
                                    @foreach ($items as $item)
                                        @php
                                            $label = $item['title'] ?? '';
                                            $icon = $item['icon'] ?? '';
                                            $gradient = $item['gradient'] ?? 'linear-gradient(135deg, #e5e7eb, #ffffff)';
                                        @endphp
                                        <article class="category-slide" data-carousel-item>
                                            <a href="{{ $item['url'] ?? '#' }}" class="group">
                                                <div class="category-avatar" style="background: {{ $gradient }};">
                                                    @if (!empty($item['image_url']))
                                                        <img src="{{ $item['image_url'] }}" alt="{{ $label }}">
                                                    @elseif (!empty($icon))
                                                        <span>{{ $icon }}</span>
                                                    @else
                                                        <span>{{ strtoupper(substr($label, 0, 1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-4 text-center">
                                                    <p class="text-base font-semibold text-slate-900">{{ $label }}</p>
                                                    <p class="text-sm text-slate-500">{{ $item['count'] ?? 0 }} items</p>
                                                </div>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                                <button class="carousel-btn" type="button" aria-label="Next categories" data-carousel-next>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06-1.06L11.69 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </section>
                    @elseif ($type === 'collection_showcase')
                        @php
                            $data = $section['data'] ?? [];
                            $items = $data['items'] ?? [];
                            $badgeImage = $data['badge_image'] ?? '';
                            $badgeAlt = $data['badge_alt'] ?? ($data['kicker'] ?? 'Ethical suppliers');
                            $items = array_values($items);
                            if (empty($items)) {
                                $items = [
                                    ['label' => 'Birthdays', 'image_url' => '', 'url' => '/shop'],
                                    ['label' => 'Corporate Tshirts', 'image_url' => '', 'url' => '/shop'],
                                    ['label' => 'Couple', 'image_url' => '', 'url' => '/shop'],
                                ];
                            }
                        @endphp
                        <section class="mx-auto max-w-6xl px-4 pb-10">
                            <div class="collection-showcase">
                                <div class="collection-hero">
                                    @if (!empty($badgeImage))
                                        <img src="{{ $badgeImage }}" alt="{{ $badgeAlt }}" class="collection-hero-badge-image">
                                    @elseif (!empty($data['kicker']))
                                        <span class="collection-hero-badge-text">{{ $data['kicker'] }}</span>
                                    @endif
                                    <div class="collection-hero-media">
                                        @if (!empty($data['hero_image']))
                                            <img src="{{ $data['hero_image'] }}" alt="{{ $data['hero_alt'] ?? 'Design studio preview' }}">
                                        @else
                                            <div class="collection-hero-placeholder">
                                                <p>Upload a hero image</p>
                                            </div>
                                        @endif
                                    </div>
                                    @if (!empty($data['hero_note']))
                                        <p class="collection-hero-note">{{ $data['hero_note'] }}</p>
                                    @endif
                                </div>
                                <div class="collection-cards">
                                    @foreach ($items as $itemIndex => $item)
                                        @php
                                            $imageUrl = $item['image_url'] ?? '';
                                            $cardClass = 'collection-card';
                                            if ($itemIndex === count($items) - 1) {
                                                $cardClass .= ' collection-card-wide';
                                            }
                                            if (empty($imageUrl)) {
                                                $cardClass .= ' collection-card-placeholder';
                                            }
                                        @endphp
                                        <a href="{{ $item['url'] ?? '/shop' }}" class="{{ $cardClass }}" @if (!empty($imageUrl)) style="background-image: url('{{ $imageUrl }}');" @endif>
                                            <span class="collection-card-btn">{{ $item['label'] ?? 'Collection' }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @elseif ($type === 'best_sellers')
                        @php
                            $data = $section['data'] ?? [];
                            $bestSellers = $featuredProducts->count() ? $featuredProducts : collect($products ?? []);
                            $fallbackSwatches = ['#111827', '#f1f5f9', '#c4b5fd', '#fca5a5', '#fde68a', '#a7f3d0'];
                        @endphp
                        <section class="mx-auto max-w-6xl px-4 py-10">
                            <div class="flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="section-kicker">{{ $data['kicker'] ?? '' }}</p>
                                    <h2 class="section-title">{{ $data['title'] ?? '' }}</h2>
                                </div>
                                <div class="flex items-center gap-2 text-xs uppercase tracking-[0.3em] text-slate-500">
                                    @if (!empty($data['meta_left']))
                                        <span>{{ $data['meta_left'] }}</span>
                                    @endif
                                    <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                                    @if (!empty($data['meta_right']))
                                        <span>{{ $data['meta_right'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($bestSellers as $index => $product)
                                    @php
                                        $isModel = is_object($product);
                                        $name = $product['title'] ?? $product['name'] ?? ($isModel ? $product->name : 'Product');
                                        $price = $product['price'] ?? ($isModel ? $product->price : 0);
                                        $rawPrice = is_numeric($price) ? (float) $price : (float) preg_replace('/[^\d.]/', '', (string) $price);
                                        $discountType = $product['discount_type'] ?? ($isModel ? $product->discount_type : 'none');
                                        $discountType = $discountType ?: 'none';
                                        $discountValue = (float) ($product['discount_value'] ?? ($isModel ? $product->discount_value : 0));
                                        $discountLabel = '';
                                        $finalPrice = $rawPrice;
                                        if ($discountType === 'percent' && $discountValue > 0) {
                                            $finalPrice = $rawPrice * (1 - ($discountValue / 100));
                                            $discountLabel = '-' . rtrim(rtrim(number_format($discountValue, 0), '0'), '.') . '%';
                                        } elseif ($discountType === 'amount' && $discountValue > 0) {
                                            $finalPrice = max($rawPrice - $discountValue, 0);
                                            $discountPercent = $rawPrice > 0 ? (int) round(($discountValue / $rawPrice) * 100) : 0;
                                            $discountLabel = $discountPercent > 0 ? '-' . $discountPercent . '%' : '-KSh ' . number_format($discountValue, 0);
                                        }
                                        $isNew = $product['is_new'] ?? ($isModel && $product->created_at ? $product->created_at->gt(now()->subDays(21)) : false);
                                        $badgeLabel = $discountLabel ?: ($isNew ? 'NEW' : '');
                                        $badgeClass = $discountLabel ? 'product-badge-sale' : ($isNew ? 'product-badge-new' : '');
                                        $imageUrl = $isModel ? ($product->image_url ?? null) : ($product['image_url'] ?? null);
                                        $detailUrl = $isModel && isset($product->id)
                                            ? route('products.show', ['product' => ($product->slug ?: $product->id)])
                                            : route('shop');
                                        $productId = $isModel && isset($product->id) ? $product->id : ('fallback-' . md5((string) $name));
                                        $hoverImage = null;
                                        if ($isModel) {
                                            $alt = $product->alternateImages->first() ?: $product->galleryImages->first();
                                            if ($alt) {
                                                $hoverImage = \Illuminate\Support\Facades\Storage::url($alt->image_path);
                                            }
                                        }
                                        $colorOptions = $isModel ? ($product->color_options ?? []) : ($product['color_options'] ?? []);
                                        if (is_string($colorOptions)) {
                                            $colorOptions = array_filter(array_map('trim', explode(',', $colorOptions)));
                                        }
                                        if (empty($colorOptions)) {
                                            $start = $index % count($fallbackSwatches);
                                            $colorOptions = array_slice(array_merge($fallbackSwatches, $fallbackSwatches), $start, 3);
                                        }
                                        $sizes = $isModel ? ($product->sizes?->pluck('name')->all() ?? []) : ($product['sizes'] ?? []);
                                        $sizes = array_slice(array_values(array_filter($sizes)), 0, 4);
                                        $rating = $product['rating'] ?? ($isModel ? ($product->rating ?? 4.8) : 4.8);
                                        $rating = is_numeric($rating) ? (float) $rating : 4.8;
                                        $filledStars = max(0, min(5, (int) round($rating)));
                                    @endphp
                                    <article class="product-card" data-product-panel>
                                        <div class="product-media">
                                            @if ($detailUrl)
                                                <a href="{{ $detailUrl }}" class="product-media-link">
                                            @endif
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy" class="product-media-img is-primary">
                                                @if ($hoverImage)
                                                    <img src="{{ $hoverImage }}" alt="{{ $name }} alternate" loading="lazy" class="product-media-img is-secondary">
                                                @endif
                                            @else
                                                <div class="product-media-placeholder">
                                                    <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                            @if ($detailUrl)
                                                </a>
                                            @endif
                                            @if (!empty($badgeLabel))
                                                <span class="product-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                            @endif
                                            <div class="product-actions">
                                                <button class="product-card-action"
                                                        type="button"
                                                        aria-label="Add to wishlist"
                                                        data-wishlist-toggle
                                                        data-product-id="{{ $productId }}"
                                                        data-product-name="{{ $name }}"
                                                        data-product-price="{{ $finalPrice }}"
                                                        data-product-image="{{ $imageUrl }}"
                                                        data-product-url="{{ $detailUrl }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                        <path d="M20.8 6.6a5.3 5.3 0 0 0-7.5 0L12 7.9l-1.3-1.3a5.3 5.3 0 1 0-7.5 7.5l1.3 1.3L12 21l7.5-5.6 1.3-1.3a5.3 5.3 0 0 0 0-7.5Z"/>
                                                    </svg>
                                                </button>
                                                <button class="product-card-action" type="button" aria-label="Compare">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M7 7h10l-3-3"/>
                                                        <path d="M17 17H7l3 3"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="product-quick">
                                            <button class="product-quick-add" type="button"
                                                    data-add-to-cart
                                                    data-product-id="{{ $productId }}"
                                                    data-product-name="{{ $name }}"
                                                    data-product-price="{{ $finalPrice }}"
                                                    data-product-image="{{ $imageUrl }}"
                                                    data-product-url="{{ $detailUrl }}">
                                                Quick Add
                                            </button>
                                            @if (!empty($sizes))
                                                <div class="product-sizes" data-option-group="size">
                                                    @foreach ($sizes as $sizeIndex => $size)
                                                        <button class="product-size-btn @if ($sizeIndex === 0) is-active @endif"
                                                                type="button"
                                                                data-option-item
                                                                data-option-value="{{ $size }}">{{ $size }}</button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mt-4 space-y-2 product-card-body">
                                            <div>
                                                <h3 class="product-card-title">{{ $name }}</h3>
                                            </div>
                                            <div class="product-card-price">
                                                <span class="product-card-price-current">KSh {{ number_format($finalPrice, 0) }}</span>
                                                @if ($finalPrice < $rawPrice)
                                                    <span class="product-card-price-old">KSh {{ number_format($rawPrice, 0) }}</span>
                                                @endif
                                            </div>
                                            @if (!empty($colorOptions))
                                                <div class="product-color-swatches" data-option-group="color">
                                                    @foreach (array_slice($colorOptions, 0, 4) as $colorIndex => $color)
                                                        <button class="product-color-dot @if ($colorIndex === 0) is-active @endif"
                                                                type="button"
                                                                style="--swatch-color: {{ $color }};"
                                                                title="{{ $color }}"
                                                                data-option-item
                                                                data-option-value="{{ $color }}">
                                                            <span class="sr-only">{{ $color }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @elseif ($type === 'gift_finder')
                        @php
                            $data = $section['data'] ?? [];
                            $giftFinderTitle = $data['title'] ?? 'Need to Find a Gift?';
                            $giftFinderLeadBefore = $data['lead_before'] ?? 'Find the perfect';
                            $giftFinderLeadAfter = $data['lead_after'] ?? 'for your favorite:';
                            $giftFinderOptions = $data['select_options'] ?? ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                            if (!is_array($giftFinderOptions)) {
                                $giftFinderOptions = array_filter(array_map('trim', explode(',', (string) $giftFinderOptions)));
                            }
                            if (empty($giftFinderOptions)) {
                                $giftFinderOptions = ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                            }
                            $giftFinderCtaLabel = $data['cta_label'] ?? 'View more designs';
                            $giftFinderCtaUrl = $data['cta_url'] ?? '/shop';
                            $giftFinderInterval = $data['carousel_interval'] ?? 5;
                            $giftFinderInterval = is_numeric($giftFinderInterval) ? max(1, (int) $giftFinderInterval) : 5;
                            $giftFinderCategoryId = $data['product_category_id'] ?? null;
                            $giftFinderCategoryId = is_numeric($giftFinderCategoryId) ? (int) $giftFinderCategoryId : null;
                            if ($giftFinderCategoryId && ($giftFinderCtaUrl === '/shop' || trim((string) $giftFinderCtaUrl) === '')) {
                                $giftFinderCtaUrl = route('shop', ['category' => $giftFinderCategoryId]);
                            }
                        @endphp
                        <section class="gift-finder">
                            <div class="gift-finder-inner">
                                <h2 class="gift-finder-title">{{ $giftFinderTitle }}</h2>
                                <div class="gift-finder-select">
                                    <span>{{ $giftFinderLeadBefore }}</span>
                                    <div class="gift-finder-select-wrap">
                                        <select aria-label="Select product"
                                                data-gift-category-select
                                                data-gift-fallback-url="{{ $giftFinderCtaUrl }}">
                                            <option value="" selected disabled>Choose category</option>
                                            @foreach ($giftFinderOptions as $optionIndex => $option)
                                                @php
                                                    $optionUrl = $resolveGiftFinderOptionUrl($option, $giftFinderCtaUrl, $giftFinderCategoryId);
                                                @endphp
                                                <option value="{{ $optionUrl }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span>{{ $giftFinderLeadAfter }}</span>
                                </div>
                                <div class="gift-finder-carousel" data-gift-carousel data-carousel-interval="{{ $giftFinderInterval }}">
                                    <div class="gift-finder-track" data-gift-track>
                                        @foreach ($giftProducts as $product)
                                            <article class="gift-product-card">
                                                <a class="gift-product-image" href="{{ $product['url'] }}">
                                                    @if (!empty($product['image']))
                                                        <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">
                                                    @else
                                                        <svg class="gift-tee" viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path class="gift-tee-body" d="M120 140l70-50h220l70 50 70 40-40 70-60-30v380a40 40 0 0 1-40 40H160a40 40 0 0 1-40-40V220l-60 30-40-70 70-40z"/>
                                                            <path class="gift-tee-shadow" d="M185 150h230l55 35-25 45-48-22v350H203V208l-48 22-25-45 55-35z"/>
                                                            <circle class="gift-tee-neck" cx="300" cy="135" r="35"/>
                                                            <rect class="gift-tee-graphic" x="215" y="260" width="170" height="160" rx="28"/>
                                                            <circle class="gift-tee-accent" cx="300" cy="340" r="46"/>
                                                            <path class="gift-tee-stroke" d="M260 330h80M260 360h80" />
                                                        </svg>
                                                    @endif
                                                </a>
                                                <div class="gift-product-body">
                                                    <h3 class="gift-product-title">{{ $product['title'] }}</h3>
                                                    <p class="gift-product-meta">{{ $product['author'] }}</p>
                                                    <p class="gift-product-price">{{ $product['price'] }}</p>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="gift-finder-cta">
                                    <a class="gift-finder-btn" href="{{ $giftFinderCtaUrl }}">{{ $giftFinderCtaLabel }}</a>
                                </div>
                            </div>
                        </section>
                    @elseif ($type === 'how_it_works')
                        @php
                            $data = $section['data'] ?? [];
                            $steps = $data['steps'] ?? [];
                            $ctaData = null;
                            for ($j = $i + 1; $j < count($sections); $j++) {
                                if (($sections[$j]['type'] ?? '') === 'cta') {
                                    $ctaData = $sections[$j]['data'] ?? [];
                                    break;
                                }
                            }
                            $hasCta = !empty($ctaData);
                            $ctaRendered = $ctaRendered || $hasCta;
                            $gridClass = $hasCta ? 'lg:grid-cols-[1.2fr_1fr]' : 'lg:grid-cols-1';
                        @endphp
                        @if (!$hasGiftFinderSection)
                            @php
                                $giftFinder = $data['gift_finder'] ?? [];
                                $giftFinderTitle = $giftFinder['title'] ?? 'Need to Find a Gift?';
                                $giftFinderLeadBefore = $giftFinder['lead_before'] ?? 'Find the perfect';
                                $giftFinderLeadAfter = $giftFinder['lead_after'] ?? 'for your favorite:';
                                $giftFinderOptions = $giftFinder['select_options'] ?? ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                                if (!is_array($giftFinderOptions)) {
                                    $giftFinderOptions = array_filter(array_map('trim', explode(',', (string) $giftFinderOptions)));
                                }
                                if (empty($giftFinderOptions)) {
                                    $giftFinderOptions = ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                                }
                                $giftFinderCtaLabel = $giftFinder['cta_label'] ?? 'View more designs';
                                $giftFinderCtaUrl = $giftFinder['cta_url'] ?? '/shop';
                                $giftFinderInterval = $giftFinder['carousel_interval'] ?? 5;
                                $giftFinderInterval = is_numeric($giftFinderInterval) ? max(1, (int) $giftFinderInterval) : 5;
                                $giftFinderCategoryId = $giftFinder['product_category_id'] ?? null;
                                $giftFinderCategoryId = is_numeric($giftFinderCategoryId) ? (int) $giftFinderCategoryId : null;
                                if ($giftFinderCategoryId && ($giftFinderCtaUrl === '/shop' || trim((string) $giftFinderCtaUrl) === '')) {
                                    $giftFinderCtaUrl = route('shop', ['category' => $giftFinderCategoryId]);
                                }
                            @endphp
                            <section class="gift-finder">
                                <div class="gift-finder-inner">
                                    <h2 class="gift-finder-title">{{ $giftFinderTitle }}</h2>
                                    <div class="gift-finder-select">
                                        <span>{{ $giftFinderLeadBefore }}</span>
                                        <div class="gift-finder-select-wrap">
                                            <select aria-label="Select product"
                                                    data-gift-category-select
                                                    data-gift-fallback-url="{{ $giftFinderCtaUrl }}">
                                                <option value="" selected disabled>Choose category</option>
                                                @foreach ($giftFinderOptions as $optionIndex => $option)
                                                    @php
                                                        $optionUrl = $resolveGiftFinderOptionUrl($option, $giftFinderCtaUrl, $giftFinderCategoryId);
                                                    @endphp
                                                    <option value="{{ $optionUrl }}">{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <span>{{ $giftFinderLeadAfter }}</span>
                                    </div>
                                    <div class="gift-finder-carousel" data-gift-carousel data-carousel-interval="{{ $giftFinderInterval }}">
                                        <div class="gift-finder-track" data-gift-track>
                                            @foreach ($giftProducts as $product)
                                                <article class="gift-product-card">
                                                    <a class="gift-product-image" href="{{ $product['url'] }}">
                                                        @if (!empty($product['image']))
                                                            <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">
                                                        @else
                                                            <svg class="gift-tee" viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <path class="gift-tee-body" d="M120 140l70-50h220l70 50 70 40-40 70-60-30v380a40 40 0 0 1-40 40H160a40 40 0 0 1-40-40V220l-60 30-40-70 70-40z"/>
                                                                <path class="gift-tee-shadow" d="M185 150h230l55 35-25 45-48-22v350H203V208l-48 22-25-45 55-35z"/>
                                                                <circle class="gift-tee-neck" cx="300" cy="135" r="35"/>
                                                                <rect class="gift-tee-graphic" x="215" y="260" width="170" height="160" rx="28"/>
                                                                <circle class="gift-tee-accent" cx="300" cy="340" r="46"/>
                                                                <path class="gift-tee-stroke" d="M260 330h80M260 360h80" />
                                                            </svg>
                                                        @endif
                                                    </a>
                                                    <div class="gift-product-body">
                                                        <h3 class="gift-product-title">{{ $product['title'] }}</h3>
                                                        <p class="gift-product-meta">{{ $product['author'] }}</p>
                                                        <p class="gift-product-price">{{ $product['price'] }}</p>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="gift-finder-cta">
                                        <a class="gift-finder-btn" href="{{ $giftFinderCtaUrl }}">{{ $giftFinderCtaLabel }}</a>
                                    </div>
                                </div>
                            </section>
                        @endif
                        <section class="mx-auto max-w-6xl px-4 pb-12">
                            <div class="grid gap-6 {{ $gridClass }}">
                                <div class="rounded-[32px] bg-white p-8 shadow-soft">
                                    <p class="section-kicker">{{ $data['kicker'] ?? '' }}</p>
                                    <h2 class="section-title">{{ $data['title'] ?? '' }}</h2>
                                    <div class="mt-6 grid gap-4">
                                        @foreach ($steps as $stepIndex => $step)
                                            <div class="step-card">
                                                <span class="step-number">{{ str_pad((string) ($stepIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                                <div>
                                                    <h3 class="text-lg font-semibold">{{ $step['title'] ?? '' }}</h3>
                                                    <p class="text-sm text-slate-600">{{ $step['body'] ?? '' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($hasCta)
                                @php
                                    $ctaPrimaryLabel = trim((string) ($ctaData['primary']['label'] ?? ''));
                                    $ctaPrimaryUrl = $resolveContactCtaUrl($ctaPrimaryLabel, $ctaData['primary']['url'] ?? '#');
                                @endphp
                                <div class="cta-card">
                                    <p class="cta-kicker">{{ $ctaData['kicker'] ?? '' }}</p>
                                    <h2 class="mt-4 text-3xl font-semibold text-white">{{ $ctaData['title'] ?? '' }}</h2>
                                    <p class="mt-4 text-sm text-white/80">
                                        {{ $ctaData['body'] ?? '' }}
                                    </p>
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        @if ($ctaPrimaryLabel !== '')
                                            <a href="{{ $ctaPrimaryUrl }}" class="btn-primary">{{ $ctaPrimaryLabel }}</a>
                                        @endif
                                        @if (!empty($ctaData['secondary']['label']))
                                            <a href="{{ $ctaData['secondary']['url'] ?? '#' }}" class="btn-secondary border-white/30 text-white hover:bg-white/10">
                                                {{ $ctaData['secondary']['label'] }}
                                            </a>
                                        @endif
                                    </div>
                                    @if (!empty($ctaData['stats']))
                                        <div class="mt-10 grid grid-cols-2 gap-4 text-sm text-white/80">
                                            @foreach ($ctaData['stats'] as $stat)
                                                <div>
                                                    <p class="text-xl font-semibold text-white">{{ $stat['value'] ?? '' }}</p>
                                                    <p>{{ $stat['label'] ?? '' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </section>
                    @elseif ($type === 'cta')
                        @if (!$ctaRendered)
                            @php
                                $data = $section['data'] ?? [];
                                $ctaPrimaryLabel = trim((string) ($data['primary']['label'] ?? ''));
                                $ctaPrimaryUrl = $resolveContactCtaUrl($ctaPrimaryLabel, $data['primary']['url'] ?? '#');
                                $ctaRendered = true;
                            @endphp
                            <section class="mx-auto max-w-6xl px-4 pb-12">
                                <div class="cta-card">
                                    <p class="cta-kicker">{{ $data['kicker'] ?? '' }}</p>
                                    <h2 class="mt-4 text-3xl font-semibold text-white">{{ $data['title'] ?? '' }}</h2>
                                    <p class="mt-4 text-sm text-white/80">
                                        {{ $data['body'] ?? '' }}
                                    </p>
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        @if ($ctaPrimaryLabel !== '')
                                            <a href="{{ $ctaPrimaryUrl }}" class="btn-primary">{{ $ctaPrimaryLabel }}</a>
                                        @endif
                                        @if (!empty($data['secondary']['label']))
                                            <a href="{{ $data['secondary']['url'] ?? '#' }}" class="btn-secondary border-white/30 text-white hover:bg-white/10">
                                                {{ $data['secondary']['label'] }}
                                            </a>
                                        @endif
                                    </div>
                                    @if (!empty($data['stats']))
                                        <div class="mt-10 grid grid-cols-2 gap-4 text-sm text-white/80">
                                            @foreach ($data['stats'] as $stat)
                                                <div>
                                                    <p class="text-xl font-semibold text-white">{{ $stat['value'] ?? '' }}</p>
                                                    <p>{{ $stat['label'] ?? '' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endif
                    @elseif ($type === 'design_studio')
                        @php
                            $data = $section['data'] ?? [];
                            $backgroundImage = $data['background_image'] ?? '';
                            $backgroundStyle = $backgroundImage ? "background-image: url('{$backgroundImage}');" : '';
                        @endphp
                        <section class="mx-auto max-w-6xl px-4 pb-14">
                            <div class="design-studio-card shadow-soft {{ $backgroundImage ? 'has-bg' : '' }}"
                                 @if ($backgroundStyle) style="{{ $backgroundStyle }}" @endif>
                                <div class="design-studio-content flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="section-kicker">{{ $data['kicker'] ?? '' }}</p>
                                        <h2 class="section-title">{{ $data['title'] ?? '' }}</h2>
                                        <p class="mt-3 text-sm text-slate-600">
                                            {{ $data['body'] ?? '' }}
                                        </p>
                                    </div>
                                    @if (!empty($data['button']['label']))
                                        <a href="{{ $data['button']['url'] ?? '#' }}" class="btn-brand">{{ $data['button']['label'] }}</a>
                                    @endif
                                </div>
                            </div>
                        </section>
                        <section class="mx-auto max-w-6xl px-4 pb-14">
                            <div class="newsletter-card">
                                <div class="newsletter-content">
                                    <div class="newsletter-copy">
                                        <h2>Subscribe to Our Newsletter</h2>
                                        <p>For sales, exclusive content, and more!</p>
                                    </div>
                                    <form class="newsletter-form" action="{{ route('newsletter.subscribe') }}" method="post">
                                        @csrf
                                        <label class="newsletter-label" for="newsletter-email">
                                            Email Address <span>*</span>
                                        </label>
                                        <div class="newsletter-input-row">
                                            <input id="newsletter-email"
                                                   name="email"
                                                   type="email"
                                                   required
                                                   placeholder=""
                                                   class="newsletter-input">
                                            <button type="submit" class="newsletter-btn">Subscribe</button>
                                        </div>
                                        @if (session('newsletter_subscribed'))
                                            <p class="mt-3 text-sm text-emerald-700">Thanks for subscribing. Check your inbox for updates.</p>
                                        @endif
                                        <p class="newsletter-legal">
                                            By clicking Subscribe, you agree to our
                                            <a href="/privacy-policy">Privacy Policy</a>
                                            and to receive our promotional emails (opt out anytime).
                                        </p>
                                    </form>
                                </div>
                            </div>
                        </section>
                    @elseif ($type === 'footer_notice')
                        @php
                            $data = $section['data'] ?? [];
                            $headline = trim((string) ($data['headline'] ?? ''));
                            $message = trim((string) ($data['message'] ?? ''));
                            $sanitizeHexColor = static function ($value, $fallback) {
                                return is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value)
                                    ? $value
                                    : $fallback;
                            };
                            $backgroundColor = $sanitizeHexColor($data['background_color'] ?? '', '#efefef');
                            $barColor = $sanitizeHexColor($data['bar_color'] ?? '', '#ff4b00');
                            $textColor = $sanitizeHexColor($data['text_color'] ?? '', '#0f172a');
                        @endphp
                        <section class="footer-notice" role="note" aria-label="Promotional notice">
                            <div class="footer-notice-shell"
                                 style="--footer-notice-bg: {{ $backgroundColor }}; --footer-notice-bar: {{ $barColor }}; --footer-notice-text: {{ $textColor }};">
                                <div class="footer-notice-content">
                                    <p class="footer-notice-copy">
                                        @if ($headline !== '')
                                            <strong>{{ $headline }}</strong>
                                        @endif
                                        @if ($message !== '')
                                            {{ $message }}
                                        @endif
                                    </p>
                                </div>
                                <div class="footer-notice-bar"></div>
                            </div>
                        </section>
                    @endif
                @endfor
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])

        </div>
    </body>
</html>
