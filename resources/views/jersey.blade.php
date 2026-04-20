<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Jersey Catalogues | Nai Prints</title>
        <meta name="description" content="Jersey sales and custom jersey branding/printing in Kenya for teams, schools, and football academies.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
        <style>
            .jersey-slider-track::-webkit-scrollbar {
                display: none;
            }
        </style>
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
                    $video = $data['video'] ?? ($defaults['video'] ?? []);
                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);

                    $catalogKicker = trim((string) ($hero['kicker'] ?? ''));
                    $catalogTitle = trim((string) (($designs['title'] ?? '') ?: ($hero['title'] ?? 'Jersey Katalog')));
                    $catalogIntro = trim((string) (($hero['body'] ?? '') ?: 'Switch to realistic 3D jersey mockups so your audience can view each design clearly from multiple angles.'));
                    $catalogBuyUrl = route('designer.3d');
                    $videoKicker = trim((string) ($video['kicker'] ?? ''));
                    $videoTitle = trim((string) ($video['title'] ?? 'Jersey Preview Video'));
                    $videoEmbedUrl = trim((string) ($video['embed_url'] ?? ''));
                    $videoMp4Url = trim((string) ($video['video_url'] ?? ''));
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
                            <span class="block text-[32px] tracking-[0.08em] text-white sm:text-[38px]">CATALOGUES</span>
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
                        <div class="relative" data-jersey-slider>
                            <button
                                type="button"
                                data-slider-prev
                                class="absolute left-0 top-[38%] z-20 hidden h-11 w-11 -translate-x-1/2 items-center justify-center rounded-full border border-white/20 bg-black/75 text-2xl text-white backdrop-blur sm:flex"
                                aria-label="Previous slide">
                                ‹
                            </button>
                            <button
                                type="button"
                                data-slider-next
                                class="absolute right-0 top-[38%] z-20 hidden h-11 w-11 translate-x-1/2 items-center justify-center rounded-full border border-white/20 bg-black/75 text-2xl text-white backdrop-blur sm:flex"
                                aria-label="Next slide">
                                ›
                            </button>

                            <div
                                data-slider-track
                                class="jersey-slider-track overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 [scrollbar-width:none] [-ms-overflow-style:none]">
                                <div class="flex w-max gap-3 sm:gap-4">
                                    @foreach ($catalogItems as $item)
                                        <article class="w-[230px] shrink-0 snap-start overflow-hidden rounded-lg border border-white/10 bg-[#111317] shadow-[0_10px_30px_rgba(0,0,0,0.45)] sm:w-[238px] lg:w-[214px]">
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
                            </div>
                        </div>
                    @endif
                </section>

                <section class="mt-12 rounded-2xl border border-white/10 bg-[#0b0d11] px-5 py-6 sm:px-8 sm:py-8">
                    <div class="mb-5">
                        @if ($videoKicker !== '')
                            <p class="text-xs uppercase tracking-[0.3em] text-zinc-400">{{ $videoKicker }}</p>
                        @endif
                        <h2 class="mt-2 text-xl font-semibold text-white sm:text-2xl">{{ $videoTitle !== '' ? $videoTitle : 'Jersey Preview Video' }}</h2>
                    </div>
                    @if ($videoMp4Url !== '')
                        <div class="overflow-hidden rounded-xl border border-white/10 bg-black">
                            <video class="aspect-video w-full" controls preload="metadata" playsinline>
                                <source src="{{ $videoMp4Url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @elseif ($videoEmbedUrl !== '')
                        <div class="overflow-hidden rounded-xl border border-white/10 bg-black">
                            <div class="aspect-video w-full">
                                <iframe
                                    class="h-full w-full"
                                    src="{{ $videoEmbedUrl }}"
                                    title="{{ $videoTitle !== '' ? $videoTitle : 'Jersey Video' }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-white/10 bg-black/40 px-4 py-6 text-sm text-zinc-300">
                            Add a video URL in the Jersey page builder to display it here.
                        </div>
                    @endif
                </section>

                <section class="mt-14 rounded-2xl border border-white/10 bg-[#0b0d11] px-5 py-8 sm:px-8">
                    <h2 class="text-2xl font-semibold text-white sm:text-3xl">Kenya-Based Custom Jersey Printing & Branding</h2>
                    <p class="mt-4 text-sm leading-relaxed text-zinc-300 sm:text-base">
                        Nai Prints provides Kenya-based jersey branding solutions for football clubs, school teams, academies, church leagues,
                        corporates, and tournaments. We handle full kit customization including player names, numbers, sponsor logos, and color
                        matching so your team looks professional on and off the pitch.
                    </p>

                    <div class="mt-7 grid gap-4 md:grid-cols-2">
                        <article class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <h3 class="text-base font-semibold text-white">Fast Production & Nationwide Delivery</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-300">
                                We support urgent and scheduled jersey orders from Nairobi and deliver across Kenya including Mombasa, Kisumu,
                                Nakuru, Eldoret, Thika, and surrounding towns. Typical production windows depend on quantity, design complexity,
                                and kit type.
                            </p>
                        </article>
                        <article class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <h3 class="text-base font-semibold text-white">Built for Teams, Schools & Academies</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-300">
                                From 10-piece team sets to larger club and school batches, we provide consistent sizing, readable numbering, and
                                durable print quality for training and match-day use.
                            </p>
                        </article>
                        <article class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <h3 class="text-base font-semibold text-white">Durable Fabrics for Kenyan Conditions</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-300">
                                Our sportswear options are selected for comfort, breathability, and repeat use. We focus on color retention and
                                print durability to keep kits sharp through regular washing and competitive seasons.
                            </p>
                        </article>
                        <article class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <h3 class="text-base font-semibold text-white">3D Preview Before You Order</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-300">
                                Use our 3D designer to preview front, back, and sleeve details before confirming your order. This helps reduce
                                approval delays and ensures your final jersey design matches team expectations.
                            </p>
                        </article>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">Popular Service Areas In Kenya</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Kiambu', 'Machakos', 'Meru', 'Nyeri'] as $town)
                                <span class="rounded-full border border-white/15 bg-white/[0.03] px-3 py-1 text-xs text-zinc-200">{{ $town }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('designer.3d') }}" class="rounded bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                            Open 3D Designer
                        </a>
                        <a href="/contact" class="rounded border border-white/25 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                            Request Team Quote
                        </a>
                    </div>
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
        <script>
            (() => {
                const sliders = document.querySelectorAll('[data-jersey-slider]');
                sliders.forEach((slider) => {
                    const track = slider.querySelector('[data-slider-track]');
                    const prev = slider.querySelector('[data-slider-prev]');
                    const next = slider.querySelector('[data-slider-next]');
                    if (!track || !prev || !next) return;

                    const getStep = () => {
                        const firstCard = track.querySelector('article');
                        if (!firstCard) return Math.max(track.clientWidth * 0.8, 260);
                        const gap = Number.parseFloat(getComputedStyle(track.firstElementChild).columnGap || '0') || 0;
                        return firstCard.getBoundingClientRect().width + gap;
                    };

                    prev.addEventListener('click', () => {
                        track.scrollBy({ left: -getStep(), behavior: 'smooth' });
                    });
                    next.addEventListener('click', () => {
                        track.scrollBy({ left: getStep(), behavior: 'smooth' });
                    });
                });
            })();
        </script>
    </body>
</html>
