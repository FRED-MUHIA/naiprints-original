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
        <title>Jersey Sales & Branding Kenya | Nai Prints</title>
        <meta name="description" content="Jersey sales and custom jersey branding/printing in Kenya for teams, schools, and football academies.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-12">
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $section = collect($sections)->firstWhere('type', 'jersey_page');
                    $defaults = \App\Support\PageDefaults::jersey()['sections'][0]['data'] ?? [];
                    $data = $section['data'] ?? [];

                    $hero = $data['hero'] ?? ($defaults['hero'] ?? []);
                    $heroStats = $hero['stats'] ?? ($defaults['hero']['stats'] ?? []);
                    $audiences = $data['audiences'] ?? ($defaults['audiences'] ?? []);
                    $audienceItems = $audiences['items'] ?? ($defaults['audiences']['items'] ?? []);
                    $designs = $data['designs'] ?? ($defaults['designs'] ?? []);
                    $designItems = $designs['items'] ?? ($defaults['designs']['items'] ?? []);
                    $video = $data['video'] ?? ($defaults['video'] ?? []);
                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);
                @endphp

                <section class="hero-card">
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
                        <p class="mt-4 max-w-3xl text-sm text-white/85 md:text-base">
                            {{ $hero['body'] }}
                        </p>
                    @endif
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @if (!empty($hero['primary']['label']))
                            <a href="{{ $hero['primary']['url'] ?? '#' }}" class="btn-primary">{{ $hero['primary']['label'] }}</a>
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
                </section>

                <section class="mt-12">
                    @if (!empty($audiences['kicker']))
                        <p class="section-kicker">{{ $audiences['kicker'] }}</p>
                    @endif
                    @if (!empty($audiences['title']))
                        <h2 class="section-title">{{ $audiences['title'] }}</h2>
                    @endif
                    <div class="mt-6 grid gap-6 md:grid-cols-3">
                        @foreach ($audienceItems as $item)
                            <article class="category-card">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-3 text-sm text-slate-600">{{ $item['body'] ?? '' }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="mt-12">
                    @if (!empty($designs['kicker']))
                        <p class="section-kicker">{{ $designs['kicker'] }}</p>
                    @endif
                    @if (!empty($designs['title']))
                        <h2 class="section-title">{{ $designs['title'] }}</h2>
                    @endif
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($designItems as $item)
                            <figure class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                                <img src="{{ $item['image_url'] ?? '' }}"
                                     alt="{{ $item['image_alt'] ?? 'Jersey design' }}"
                                     class="h-64 w-full object-cover">
                            </figure>
                        @endforeach
                    </div>
                </section>

                <section class="mt-12">
                    @if (!empty($video['kicker']))
                        <p class="section-kicker">{{ $video['kicker'] }}</p>
                    @endif
                    @if (!empty($video['title']))
                        <h2 class="section-title">{{ $video['title'] }}</h2>
                    @endif
                    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-black shadow-xl">
                        <div class="aspect-video w-full">
                            <iframe
                                class="h-full w-full"
                                src="{{ $video['embed_url'] ?? '' }}"
                                title="Custom Jersey Branding Video"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </section>

                <section class="mt-12">
                    <div class="cta-card">
                        <div>
                            @if (!empty($cta['title']))
                                <h2 class="text-3xl font-semibold text-white">{{ $cta['title'] }}</h2>
                            @endif
                            @if (!empty($cta['body']))
                                <p class="mt-3 text-sm text-white/80">{{ $cta['body'] }}</p>
                            @endif
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if (!empty($cta['primary']['label']))
                                <a href="{{ $cta['primary']['url'] ?? '#' }}" class="btn-primary">{{ $cta['primary']['label'] }}</a>
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
