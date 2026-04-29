<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => 'Embroidery Branding in Nairobi | Nai Prints',
            'description' => 'Professional embroidery branding in Nairobi for uniforms, caps, patches, polos, and corporate apparel tailored for your business.',
            'canonical' => route('embroidery'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-12">
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $section = collect($sections)->firstWhere('type', 'embroidery_page');
                    $defaults = \App\Support\PageDefaults::embroidery()['sections'][0]['data'] ?? [];
                    $data = $section['data'] ?? [];

                    $hero = $data['hero'] ?? ($defaults['hero'] ?? []);
                    $heroStats = $hero['stats'] ?? ($defaults['hero']['stats'] ?? []);

                    $infoCards = $data['info_cards'] ?? ($defaults['info_cards'] ?? []);

                    $services = $data['services'] ?? ($defaults['services'] ?? []);
                    $serviceItems = $services['items'] ?? ($defaults['services']['items'] ?? []);

                    $impact = $data['impact'] ?? ($defaults['impact'] ?? []);
                    $impactBullets = $impact['bullets'] ?? ($defaults['impact']['bullets'] ?? []);

                    $process = $data['process'] ?? ($defaults['process'] ?? []);
                    $processSteps = $process['steps'] ?? ($defaults['process']['steps'] ?? []);

                    $portfolio = $data['portfolio'] ?? ($defaults['portfolio'] ?? []);
                    $portfolioItems = $portfolio['items'] ?? ($defaults['portfolio']['items'] ?? []);

                    $resolveContactCtaUrl = static function ($label, $url = '#') {
                        $normalizedLabel = strtolower(trim((string) $label));
                        $resolvedUrl = trim((string) $url);
                        if (
                            in_array($normalizedLabel, ['start your order', 'request a quote'], true)
                            && ($resolvedUrl === '' || $resolvedUrl === '#' || $resolvedUrl === '/shop')
                        ) {
                            return route('contact.show');
                        }

                        return $resolvedUrl !== '' ? $resolvedUrl : '#';
                    };

                    $heroPrimaryLabel = trim((string) ($hero['primary']['label'] ?? ''));
                    $heroPrimaryUrl = $resolveContactCtaUrl($heroPrimaryLabel, $hero['primary']['url'] ?? '#');

                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);
                    $ctaPrimaryLabel = trim((string) ($cta['primary']['label'] ?? ''));
                    $ctaPrimaryUrl = $resolveContactCtaUrl($ctaPrimaryLabel, $cta['primary']['url'] ?? '#');
                @endphp

                <section class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="hero-card">
                        @if (!empty($hero['kicker']))
                            <p class="pill">{{ $hero['kicker'] }}</p>
                        @endif
                        <h1 class="mt-6 text-4xl font-semibold leading-tight text-white md:text-5xl">
                            {{ $hero['title'] ?? '' }}
                            @if (!empty($hero['highlight']))
                                <span class="text-brand">{{ $hero['highlight'] }}</span>
                            @endif
                        </h1>
                        @if (!empty($hero['body']))
                            <p class="mt-4 text-sm text-white/80 md:text-base">
                                {{ $hero['body'] }}
                            </p>
                        @endif
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            @if ($heroPrimaryLabel !== '')
                                <a href="{{ $heroPrimaryUrl }}" class="btn-primary">{{ $heroPrimaryLabel }}</a>
                            @endif
                            @if (!empty($hero['secondary']['label']))
                                <a href="{{ $hero['secondary']['url'] ?? '#' }}" class="btn-secondary">{{ $hero['secondary']['label'] }}</a>
                            @endif
                        </div>
                        @if (!empty($heroStats))
                            <div class="mt-10 grid gap-4 text-sm text-white/80 md:grid-cols-3">
                                @foreach ($heroStats as $stat)
                                    <div>
                                        <p class="text-xl font-semibold text-white">{{ $stat['value'] ?? '' }}</p>
                                        <p>{{ $stat['label'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="grid gap-4">
                        @foreach ($infoCards as $card)
                            <div class="category-card">
                                @if (!empty($card['kicker']))
                                    <p class="section-kicker">{{ $card['kicker'] }}</p>
                                @endif
                                @if (!empty($card['title']))
                                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $card['title'] }}</h2>
                                @endif
                                @if (!empty($card['body']))
                                    <p class="mt-3 text-sm text-slate-600">
                                        {{ $card['body'] }}
                                    </p>
                                @endif
                                @if (!empty($card['image_url']))
                                    <div class="embroidery-card-media has-image">
                                        <img src="{{ $card['image_url'] }}" alt="{{ $card['image_alt'] ?? '' }}">
                                    </div>
                                @else
                                    <div class="embroidery-card-media">
                                        <p>Upload an image</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mt-12">
                    <div class="mb-6">
                        @if (!empty($services['kicker']))
                            <p class="section-kicker">{{ $services['kicker'] }}</p>
                        @endif
                        @if (!empty($services['title']))
                            <h2 class="section-title">{{ $services['title'] }}</h2>
                        @endif
                    </div>
                    <div class="grid gap-6 md:grid-cols-2">
                        @foreach ($serviceItems as $item)
                            <div class="category-card embroidery-service-card">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $item['title'] ?? '' }}</h3>
                                    <p class="mt-2 text-sm text-slate-600">
                                        {{ $item['body'] ?? '' }}
                                    </p>
                                </div>
                                <div class="embroidery-service-circle" aria-hidden="true">
                                    @if (!empty($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['image_alt'] ?? '' }}">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mt-12 grid gap-6 lg:grid-cols-[1fr_1fr]">
                    <div class="category-card">
                        @if (!empty($impact['kicker']))
                            <p class="section-kicker">{{ $impact['kicker'] }}</p>
                        @endif
                        @if (!empty($impact['title']))
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $impact['title'] }}</h2>
                        @endif
                        @if (!empty($impact['body']))
                            <p class="mt-3 text-sm text-slate-600">
                                {{ $impact['body'] }}
                            </p>
                        @endif
                        @if (!empty($impactBullets))
                            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-600">
                                @foreach ($impactBullets as $bullet)
                                    @if (!empty($bullet))
                                        <li>{{ $bullet }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="category-card">
                        @if (!empty($process['kicker']))
                            <p class="section-kicker">{{ $process['kicker'] }}</p>
                        @endif
                        <div class="mt-4 grid gap-4">
                            @foreach ($processSteps as $stepIndex => $step)
                                <div class="step-card">
                                    <span class="step-number">{{ str_pad((string) ($stepIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $step['title'] ?? '' }}</p>
                                        <p class="text-sm text-slate-600">{{ $step['body'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="mt-12">
                    <div class="mb-6">
                        @if (!empty($portfolio['kicker']))
                            <p class="section-kicker">{{ $portfolio['kicker'] }}</p>
                        @endif
                        @if (!empty($portfolio['title']))
                            <h2 class="section-title">{{ $portfolio['title'] }}</h2>
                        @endif
                    </div>
                    <div class="embroidery-portfolio-grid">
                        @foreach ($portfolioItems as $item)
                            <div class="embroidery-portfolio-tile">
                                @if (!empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['image_alt'] ?? '' }}">
                                @else
                                    <span>Upload image</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mt-12">
                    <div class="cta-card">
                        <div>
                            @if (!empty($cta['kicker']))
                                <p class="cta-kicker">{{ $cta['kicker'] }}</p>
                            @endif
                            @if (!empty($cta['title']))
                                <h2 class="mt-4 text-3xl font-semibold text-white">{{ $cta['title'] }}</h2>
                            @endif
                            @if (!empty($cta['body']))
                                <p class="mt-3 text-sm text-white/80">
                                    {{ $cta['body'] }}
                                </p>
                            @endif
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($ctaPrimaryLabel !== '')
                                <a href="{{ $ctaPrimaryUrl }}" class="btn-primary">{{ $ctaPrimaryLabel }}</a>
                            @endif
                            @if (!empty($cta['secondary']['label']))
                                <a href="{{ $cta['secondary']['url'] ?? '#' }}" class="btn-secondary">{{ $cta['secondary']['label'] }}</a>
                            @endif
                        </div>
                    </div>
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
