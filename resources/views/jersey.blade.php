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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-black text-white">
        <div class="min-h-screen bg-black">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto w-full max-w-[1220px] px-4 py-10 sm:px-6 lg:px-8">
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $section = collect($sections)->firstWhere('type', 'jersey_page');
                    $defaults = \App\Support\PageDefaults::jersey()['sections'][0]['data'] ?? [];
                    $data = $section['data'] ?? [];

                    $hero = $data['hero'] ?? ($defaults['hero'] ?? []);
                    $heroImage = $hero['image'] ?? ($defaults['hero']['image'] ?? '');
                    $heroImageAlt = $hero['image_alt'] ?? ($defaults['hero']['image_alt'] ?? 'Jersey hero image');
                    $heroStats = $hero['stats'] ?? ($defaults['hero']['stats'] ?? []);
                    $audiences = $data['audiences'] ?? ($defaults['audiences'] ?? []);
                    $audienceItems = $audiences['items'] ?? ($defaults['audiences']['items'] ?? []);
                    $designs = $data['designs'] ?? ($defaults['designs'] ?? []);
                    $designItems = $designs['items'] ?? ($defaults['designs']['items'] ?? []);
                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);

                    $catalogKicker = trim((string) ($hero['kicker'] ?? ''));
                    $catalogTitle = trim((string) (($designs['title'] ?? '') ?: ($hero['title'] ?? 'Jersey Katalog')));
                    $catalogIntro = trim((string) (($hero['body'] ?? '') ?: 'Switch to realistic 3D jersey mockups so your audience can view each design clearly from multiple angles.'));
                    $catalogBuyUrl = trim((string) ($cta['primary']['url'] ?? '/contact'));
                    if ($catalogBuyUrl === '') {
                        $catalogBuyUrl = '/contact';
                    }
                    $catalogItems = collect($designItems)
                        ->filter(fn ($item) => is_array($item))
                        ->map(function ($item) {
                            $title = trim((string) ($item['title'] ?? $item['name'] ?? $item['image_alt'] ?? '3D Mockup Jersey'));
                            $price = trim((string) ($item['price'] ?? 'Rp 189.000'));
                            $image = trim((string) ($item['image_url'] ?? ''));
                            $alt = trim((string) ($item['image_alt'] ?? $title));

                            return [
                                'title' => $title !== '' ? $title : '3D Mockup Jersey',
                                'price' => $price !== '' ? $price : 'Rp 189.000',
                                'image' => $image,
                                'alt' => $alt !== '' ? $alt : 'Jersey mockup',
                            ];
                        })
                        ->values();
                @endphp

                <section>
                    <div class="mx-auto max-w-3xl text-center">
                        <p class="text-xs uppercase tracking-[0.35em] text-zinc-400">
                            {{ $catalogKicker !== '' ? $catalogKicker : 'Nai Prints' }}
                        </p>
                        <h1 class="mt-2 text-[68px] leading-[0.78] text-red-600 sm:text-[84px]" style="font-family: 'Bebas Neue', sans-serif;">
                            JERSEY
                            <span class="block text-[32px] tracking-[0.08em] text-white sm:text-[38px]">KATALOG</span>
                        </h1>
                        <p class="mx-auto mt-7 max-w-2xl text-base leading-relaxed text-zinc-400 sm:text-2xl">
                            {{ $catalogIntro }}
                        </p>
                    </div>
                </section>

                <section class="mt-12">
                    @if ($catalogItems->isEmpty())
                        <div class="rounded-2xl border border-white/10 bg-zinc-900/60 px-6 py-10 text-center">
                            <p class="text-sm text-zinc-300">No jersey mockups added yet. Add images in the admin page builder to populate this catalog.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach ($catalogItems as $item)
                                <article class="overflow-hidden rounded-lg border border-white/10 bg-[#111317] shadow-[0_10px_30px_rgba(0,0,0,0.45)]">
                                    <a href="{{ $catalogBuyUrl }}" class="block">
                                        @if ($item['image'] !== '')
                                            <img
                                                src="{{ $item['image'] }}"
                                                alt="{{ $item['alt'] }}"
                                                loading="lazy"
                                                class="h-44 w-full object-cover sm:h-52">
                                        @else
                                            <div class="flex h-44 w-full items-center justify-center bg-gradient-to-br from-zinc-700 to-zinc-900 text-xs uppercase tracking-[0.25em] text-zinc-300 sm:h-52">
                                                No Image
                                            </div>
                                        @endif
                                        <div class="px-3 pb-2 pt-2">
                                            <h3 class="text-[15px] font-semibold leading-tight text-white" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                {{ $item['title'] }}
                                            </h3>
                                            <p class="mt-1 text-sm text-zinc-300">{{ $item['price'] }}</p>
                                        </div>
                                    </a>
                                    <div class="px-3 pb-3">
                                        <a
                                            href="{{ $catalogBuyUrl }}"
                                            class="block w-full rounded bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white transition hover:bg-red-500">
                                            Beli Ini
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
