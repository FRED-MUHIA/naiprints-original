<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => 'Nai Prints | Custom T-Shirt, Hoodie & Garment Printing in Kenya',
            'description' => 'Order custom t-shirts, hoodies, uniforms, jerseys, embroidery, and branded merchandise from Nai Prints in Kenya. Design online or request a bulk quote.',
            'canonical' => url('/'),
            'schema' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => 'Nai Prints',
                    'url' => url('/'),
                    'logo' => url('/images/logo.svg'),
                    'email' => 'info@naiprints.co.ke',
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => 'Nai Prints',
                    'url' => url('/'),
                ],
            ],
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main>
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $ctaRendered = false;
                    $railRendered = false;
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
                                        $tileUrl = trim((string) ($tile['product_link'] ?? ($tile['url'] ?? '')));
                                    @endphp
                                    @if ($tileUrl !== '')
                                        <a href="{{ $tileUrl }}" class="{{ $tileClass }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                    @else
                                        <div class="{{ $tileClass }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                    @endif
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
                                    @if ($tileUrl !== '')
                                        </a>
                                    @else
                                        </div>
                                    @endif
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
                                        $tileUrl = trim((string) ($tile['product_link'] ?? ($tile['url'] ?? '')));
                                    @endphp
                                    @if ($tileUrl !== '')
                                        <a href="{{ $tileUrl }}" class="{{ $tileClass }}" data-tile-index="{{ $tileIndex }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                    @else
                                        <div class="{{ $tileClass }}" data-tile-index="{{ $tileIndex }}" @if ($tileStyle) style="{{ $tileStyle }}" @endif @if (!empty($tileVideo)) data-hero-video-tile @endif>
                                    @endif
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
                                    @if ($tileUrl !== '')
                                        </a>
                                    @else
                                        </div>
                                    @endif
                                @endforeach
                            </section>
                        @endif
                    @elseif ($type === 'product_steps')
                        @php
                            $data = $section['data'] ?? [];
                            $productSteps = $data['steps'] ?? [];
                            if (empty($productSteps)) {
                                $productSteps = \App\Support\PageDefaults::section('product_steps')['data']['steps'] ?? [];
                            }
                        @endphp
                            <section class="product-steps-section mx-auto max-w-6xl px-4 pb-10">
                                <div class="product-steps-grid">
                                    @foreach ($productSteps as $step)
                                        @php
                                            $stepTheme = $step['theme'] ?? 'products';
                                            if (!in_array($stepTheme, ['products', 'design', 'delivery'], true)) {
                                                $stepTheme = 'products';
                                            }
                                            $stepImage = trim((string) ($step['image'] ?? ''));
                                            $stepImageAlt = trim((string) ($step['image_alt'] ?? $step['title'] ?? ''));
                                        @endphp
                                        <article class="product-step">
                                            <div class="product-step-media product-step-media-{{ $stepTheme }}" @if ($stepImage === '') aria-hidden="true" @endif>
                                                @if ($stepImage !== '')
                                                    <img src="{{ $stepImage }}" alt="{{ $stepImageAlt }}" loading="lazy" class="product-step-image">
                                                @elseif ($stepTheme === 'products')
                                                    <svg class="product-step-art product-step-art-products" viewBox="0 0 420 280" role="img" aria-label="">
                                                        <rect x="94" y="52" width="224" height="174" rx="24" fill="#50d09f" opacity="0.88" />
                                                        <path d="M84 74 36 88 78 226 142 198 112 112Z" fill="#f8fafc" />
                                                        <path d="M72 93c18 12 32 11 48-5" fill="none" stroke="#d7dee9" stroke-width="5" stroke-linecap="round" />
                                                        <path d="M88 150c11-24 30-31 45-8 14 20 25 21 38 5" fill="none" stroke="#20b783" stroke-width="10" stroke-linecap="round" />
                                                        <path d="M160 58 212 42 268 58 286 92 268 108v118H160V108l-18-16Z" fill="#ffffff" />
                                                        <path d="M193 58c10 12 31 12 42 0" fill="none" stroke="#e2e8f0" stroke-width="5" stroke-linecap="round" />
                                                        <path d="M205 136 225 104l42 60h-86l22-28Z" fill="none" stroke="#243b73" stroke-width="8" stroke-linejoin="round" />
                                                        <path d="M216 102c-4-15 11-30 27-23 7 3 12 9 14 16" fill="none" stroke="#243b73" stroke-width="7" stroke-linecap="round" />
                                                        <path d="M286 122 344 144 318 216 260 194Z" fill="#ffffff" />
                                                        <path d="M286 166c19-35 46-35 63-5" fill="none" stroke="#d29f58" stroke-width="6" stroke-linecap="round" />
                                                        <path d="M294 183h42" stroke="#d29f58" stroke-width="6" stroke-linecap="round" />
                                                        <circle cx="334" cy="91" r="34" fill="#ffffff" opacity="0.94" />
                                                        <circle cx="334" cy="91" r="23" fill="none" stroke="#1f2f60" stroke-width="4" />
                                                        <path d="M324 93c6 8 19 8 25 0" fill="none" stroke="#e11d2e" stroke-width="4" stroke-linecap="round" />
                                                        <circle cx="325" cy="83" r="3" fill="#1f2f60" />
                                                        <circle cx="344" cy="83" r="3" fill="#1f2f60" />
                                                    </svg>
                                                @elseif ($stepTheme === 'design')
                                                    <svg class="product-step-art product-step-art-design" viewBox="0 0 420 280" role="img" aria-label="">
                                                        <rect x="0" y="0" width="420" height="280" fill="#37b95e" />
                                                        <path d="M54 214c54-38 116-39 170 0" fill="#9ee0b0" opacity="0.62" />
                                                        <path d="M116 206c-15-34 2-67 39-79 38-13 83 4 96 41 6 17 3 31-4 43Z" fill="#c9f1d2" opacity="0.55" />
                                                        <path d="M168 38 214 24 260 38 280 74 260 94v162H168V94l-20-20Z" fill="#ffffff" />
                                                        <path d="M196 38c8 10 28 10 36 0" fill="none" stroke="#111827" stroke-width="5" stroke-linecap="round" />
                                                        <rect x="194" y="100" width="84" height="84" fill="none" stroke="#111827" stroke-width="5" />
                                                        <path d="M207 162 227 140l15 14 12-18 14 26Z" fill="none" stroke="#111827" stroke-width="5" stroke-linejoin="round" />
                                                        <circle cx="258" cy="122" r="6" fill="#111827" />
                                                        <circle cx="292" cy="92" r="24" fill="#02ad63" />
                                                        <path d="M292 78v28M278 92h28" stroke="#ffffff" stroke-width="8" stroke-linecap="round" />
                                                        <path d="M90 236h80" stroke="#166534" stroke-width="8" stroke-linecap="round" opacity="0.8" />
                                                        <path d="M72 226h86l-10 20H82Z" fill="#a7f3d0" opacity="0.75" />
                                                    </svg>
                                                @else
                                                    <svg class="product-step-art product-step-art-delivery" viewBox="0 0 420 280" role="img" aria-label="">
                                                        <rect x="0" y="0" width="420" height="280" fill="#52c4ba" />
                                                        <ellipse cx="246" cy="232" rx="96" ry="24" fill="#2f7f8a" opacity="0.45" />
                                                        <path d="M70 202 178 154 250 188 134 238Z" fill="#f5f7fa" />
                                                        <path d="M96 216 94 254M194 188l18 52M146 210l-34 42" stroke="#9a6b40" stroke-width="8" stroke-linecap="round" />
                                                        <rect x="122" y="88" width="68" height="44" rx="3" fill="#ffffff" />
                                                        <rect x="130" y="98" width="52" height="28" fill="#dbeafe" />
                                                        <path d="M138 118h12M158 118h12M138 108h30" stroke="#f59e0b" stroke-width="4" stroke-linecap="round" />
                                                        <path d="M244 78c28 13 40 48 27 80l-16 39-60-26 19-44c-19-13-16-45 6-54 7-3 16-1 24 5Z" fill="#f4caa4" />
                                                        <path d="M232 68c24 0 31 23 18 36-8 8-23 1-27 13-18-10-19-36 9-49Z" fill="#9a6238" />
                                                        <path d="M252 126c34 11 51 42 49 89h-74l-18-42Z" fill="#dbeafe" />
                                                        <rect x="322" y="36" width="10" height="188" rx="5" fill="#e8f7f6" />
                                                        <path d="M326 64h62M326 192h66" stroke="#e8f7f6" stroke-width="8" stroke-linecap="round" />
                                                        <path d="M346 62h34v70h-34Z" fill="#243b73" />
                                                        <path d="M382 62h34v74h-34Z" fill="#e24a56" />
                                                        <path d="M352 92h20M388 92h20" stroke="#ffffff" stroke-width="4" stroke-linecap="round" opacity="0.7" />
                                                        <path d="M292 106c8 15 8 32 0 50" stroke="#f4caa4" stroke-width="16" stroke-linecap="round" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="product-step-copy">
                                                <h2>{{ $step['title'] }}</h2>
                                                <p>{{ $step['body'] }}</p>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
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

                            <div class="best-sellers-grid mt-6 grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
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
                                            <a class="product-quick-add product-quick-customize" href="{{ route('mockup') }}">
                                                Customize Now
                                            </a>
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
                    @elseif ($type === 'sale_feature')
                        @php
                            $data = $section['data'] ?? [];
                            $saleTitle = trim((string) ($data['title'] ?? 'Extra Sale 30% Off'));
                            $saleBody = trim((string) ($data['body'] ?? "What's more, we do it right. A full administration printing background."));
                            $saleDiscount = is_numeric($data['discount_percent'] ?? null) ? (float) $data['discount_percent'] : 30;
                            $saleDiscount = max(0, min(95, $saleDiscount));
                            $saleCtaLabel = trim((string) ($data['cta_label'] ?? 'Shop now'));
                            $saleCtaUrl = trim((string) ($data['cta_url'] ?? '/shop'));
                            $saleHeroImage = trim((string) ($data['hero_image'] ?? ''));
                            $saleHeroImageAlt = trim((string) ($data['hero_image_alt'] ?? $saleTitle));
                            $saleCategoryId = $data['product_category_id'] ?? null;
                            $saleCategoryId = is_numeric($saleCategoryId) ? (int) $saleCategoryId : null;
                            $sanitizeSaleColor = static function ($value, $fallback) {
                                return is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value)
                                    ? $value
                                    : $fallback;
                            };
                            $saleColors = [
                                'background' => $sanitizeSaleColor($data['background_color'] ?? '', '#ffffff'),
                                'heroBackground' => $sanitizeSaleColor($data['hero_background_color'] ?? '', '#f3f4f6'),
                                'saleText' => $sanitizeSaleColor($data['sale_text_color'] ?? '', '#e11d2e'),
                                'saleTextAlt' => $sanitizeSaleColor($data['sale_text_alt_color'] ?? '', '#000000'),
                                'saleOutline' => $sanitizeSaleColor($data['sale_outline_color'] ?? '', '#000000'),
                                'countdownBackground' => $sanitizeSaleColor($data['countdown_background_color'] ?? '', '#e11d2e'),
                                'countdownText' => $sanitizeSaleColor($data['countdown_text_color'] ?? '', '#ffffff'),
                            ];
                            if ($saleCategoryId && ($saleCtaUrl === '/shop' || $saleCtaUrl === '')) {
                                $saleCtaUrl = route('shop', ['category' => $saleCategoryId]);
                            }
                            $saleEndsAtRaw = trim((string) ($data['ends_at'] ?? ''));
                            try {
                                $saleEndsAt = $saleEndsAtRaw !== ''
                                    ? \Carbon\Carbon::parse($saleEndsAtRaw)
                                    : now()->addDays(30);
                            } catch (\Throwable $exception) {
                                $saleEndsAt = now()->addDays(30);
                            }
                            $saleEndsAtIso = $saleEndsAt->toIso8601String();
                            $saleSecondsRemaining = max(0, now()->diffInSeconds($saleEndsAt, false));
                            $saleDays = (int) floor($saleSecondsRemaining / 86400);
                            $saleHours = (int) floor(($saleSecondsRemaining % 86400) / 3600);
                            $saleMinutes = (int) floor(($saleSecondsRemaining % 3600) / 60);
                            $saleSeconds = (int) ($saleSecondsRemaining % 60);
                            $saleProductSource = !empty($saleFeatureProducts) && $saleFeatureProducts->count()
                                ? $saleFeatureProducts
                                : ($featuredProducts->count() ? $featuredProducts : collect($products ?? []));
                            $saleProducts = $saleProductSource->take(2)->values();
                        @endphp
                        <section class="sale-feature mx-auto max-w-6xl px-4 pb-12"
                                 style="--sale-feature-bg: {{ $saleColors['background'] }}; --sale-hero-bg: {{ $saleColors['heroBackground'] }}; --sale-word-color: {{ $saleColors['saleText'] }}; --sale-word-alt: {{ $saleColors['saleTextAlt'] }}; --sale-word-outline: {{ $saleColors['saleOutline'] }}; --sale-countdown-bg: {{ $saleColors['countdownBackground'] }}; --sale-countdown-text: {{ $saleColors['countdownText'] }};">
                            <div class="sale-feature-grid">
                                <div class="sale-feature-hero">
                                    @if ($saleHeroImage !== '')
                                        <img src="{{ $saleHeroImage }}" alt="{{ $saleHeroImageAlt }}" loading="lazy" class="sale-feature-hero-image">
                                    @else
                                        <div class="sale-feature-people" aria-hidden="true">
                                            <div class="sale-person sale-person-left">
                                                <div class="sale-person-head"></div>
                                                <div class="sale-person-hair"></div>
                                                <div class="sale-person-shirt">
                                                    <span></span>
                                                </div>
                                                <div class="sale-person-leg sale-person-leg-left"></div>
                                                <div class="sale-person-leg sale-person-leg-right"></div>
                                            </div>
                                            <div class="sale-person sale-person-right">
                                                <div class="sale-person-head"></div>
                                                <div class="sale-person-hair"></div>
                                                <div class="sale-person-shirt">
                                                    <span></span>
                                                </div>
                                                <div class="sale-person-leg sale-person-leg-left"></div>
                                                <div class="sale-person-leg sale-person-leg-right"></div>
                                            </div>
                                        </div>
                                    @endif
                                    <a href="{{ $saleCtaUrl !== '' ? $saleCtaUrl : route('shop') }}" class="sale-feature-button">
                                        <span>{{ $saleCtaLabel !== '' ? $saleCtaLabel : 'Shop now' }}</span>
                                        <span aria-hidden="true">-&gt;</span>
                                    </a>
                                </div>
                                <div class="sale-feature-content">
                                    <div class="sale-feature-head">
                                        <h2>{{ $saleTitle }}</h2>
                                        <p>{{ $saleBody }}</p>
                                        <div class="sale-countdown"
                                             aria-label="Sale countdown"
                                             data-sale-countdown
                                             data-sale-ends-at="{{ $saleEndsAtIso }}">
                                            <span data-sale-days>{{ str_pad((string) $saleDays, 2, '0', STR_PAD_LEFT) }}</span>
                                            <b>:</b>
                                            <span data-sale-hours>{{ str_pad((string) $saleHours, 2, '0', STR_PAD_LEFT) }}</span>
                                            <b>:</b>
                                            <span data-sale-minutes>{{ str_pad((string) $saleMinutes, 2, '0', STR_PAD_LEFT) }}</span>
                                            <b>:</b>
                                            <span data-sale-seconds>{{ str_pad((string) $saleSeconds, 2, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                    <div class="sale-product-row">
                                        <button class="sale-nav sale-nav-prev" type="button" aria-label="Previous sale product">
                                            <span aria-hidden="true">&lt;</span>
                                        </button>
                                        @foreach ($saleProducts as $saleIndex => $product)
                                            @php
                                                $isModel = is_object($product);
                                                $name = $product['title'] ?? $product['name'] ?? ($isModel ? $product->name : 'Product');
                                                $price = $product['price'] ?? ($isModel ? $product->price : 0);
                                                $rawPrice = is_numeric($price) ? (float) $price : (float) preg_replace('/[^\d.]/', '', (string) $price);
                                                $salePrice = $rawPrice > 0 ? $rawPrice * (1 - ($saleDiscount / 100)) : 0;
                                                $imageUrl = $isModel ? ($product->image_url ?? null) : ($product['image_url'] ?? null);
                                                $detailUrl = $isModel && isset($product->id)
                                                    ? route('products.show', ['product' => ($product->slug ?: $product->id)])
                                                    : route('shop');
                                                $colorOptions = $isModel ? ($product->color_options ?? []) : ($product['color_options'] ?? []);
                                                if (is_string($colorOptions)) {
                                                    $colorOptions = array_filter(array_map('trim', explode(',', $colorOptions)));
                                                }
                                                if (empty($colorOptions)) {
                                                    $colorOptions = ['#111827', '#7ed957'];
                                                }
                                            @endphp
                                            <article class="sale-mini-product">
                                                <a href="{{ $detailUrl }}" class="sale-mini-media">
                                                    <span class="sale-mini-badge">Sale!</span>
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy">
                                                    @else
                                                        <div class="sale-mini-placeholder">
                                                            <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                                                        </div>
                                                    @endif
                                                </a>
                                                <div class="sale-mini-copy">
                                                    <h3>{{ $name }}</h3>
                                                    <p>
                                                        @if ($salePrice > 0)
                                                            <span>KSh {{ number_format($salePrice, 0) }}</span>
                                                            <del>KSh {{ number_format($rawPrice, 0) }}</del>
                                                        @else
                                                            <span>Shop sale</span>
                                                        @endif
                                                    </p>
                                                    <div class="sale-mini-swatches">
                                                        @foreach (array_slice($colorOptions, 0, 2) as $color)
                                                            <span style="--sale-swatch: {{ $color }};"></span>
                                                        @endforeach
                                                        <em>+5</em>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                        <button class="sale-nav sale-nav-next" type="button" aria-label="Next sale product">
                                            <span aria-hidden="true">&gt;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @elseif ($type === 'gift_finder')
                        @php $railRendered = true; @endphp
                        @include('partials.home-clothing-rail')
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
                        @if (isset($data['gift_finder']) && !$railRendered)
                            @php $railRendered = true; @endphp
                            @include('partials.home-clothing-rail')
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
                            @include('partials.county-slider')
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
                @if (!$railRendered)
                    @include('partials.home-clothing-rail')
                @endif
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])

        </div>
    </body>
</html>
