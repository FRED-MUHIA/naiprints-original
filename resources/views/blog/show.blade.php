<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
            $canonicalUrl = request()->url();
            $publishedDateIso = optional($blog->published_at ?? $blog->created_at)->toIso8601String();
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ \Illuminate\Support\Str::limit($metaDescription, 155, '') }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($metaDescription, 155, '') }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        @if ($blog->cover_image_url)
            <meta property="og:image" content="{{ $blog->cover_image_url }}">
        @endif
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $blog->title,
                'description' => $metaDescription,
                'datePublished' => $publishedDateIso,
                'dateModified' => optional($blog->updated_at)->toIso8601String(),
                'mainEntityOfPage' => $canonicalUrl,
                'image' => $blog->cover_image_url ? [$blog->cover_image_url] : [],
                'articleSection' => $blog->category?->name,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-10">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <article class="rounded-[30px] border border-black/5 bg-white p-6 shadow-[0_24px_60px_-45px_rgba(15,23,42,0.5)] md:p-10">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            @if ($blog->category)
                                <span class="rounded-full border border-black/10 bg-black/5 px-3 py-1">{{ $blog->category->name }}</span>
                            @endif
                            <span>{{ ($blog->published_at ?? $blog->created_at)?->format('M d, Y') }}</span>
                        </div>

                        <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight md:text-4xl">{{ $blog->title }}</h1>

                        @if ($blog->excerpt)
                            <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $blog->excerpt }}</p>
                        @endif

                        @if ($blog->cover_image_url)
                            <div class="mt-6 overflow-hidden rounded-3xl border border-black/5">
                                <img src="{{ $blog->cover_image_url }}" alt="{{ $blog->title }}" class="h-full w-full object-cover">
                            </div>
                        @endif

                        @php
                            $content = (string) $blog->content;
                            $contentHasHtml = $content !== strip_tags($content);
                            $content = preg_replace_callback(
                                '/(<img[^>]+src=["\'])(?:https?:\/\/[^"\']+)?\/?storage\/([^"\']+)(["\'])/i',
                                function ($matches) {
                                    $path = ltrim((string) ($matches[2] ?? ''), '/');
                                    if ($path === '') {
                                        return $matches[0];
                                    }
                                    return $matches[1] . e(route('blogs.media', ['path' => $path])) . $matches[3];
                                },
                                $content
                            ) ?? $content;
                        @endphp
                        @if ($contentHasHtml)
                            <div class="mt-8 text-[1.02rem] leading-8 text-slate-700
                                        [&_h1]:mt-6 [&_h1]:text-3xl [&_h1]:font-semibold
                                        [&_h2]:mt-6 [&_h2]:text-2xl [&_h2]:font-semibold
                                        [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-semibold
                                        [&_p]:mt-4
                                        [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-6
                                        [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-6
                                        [&_blockquote]:mt-4 [&_blockquote]:border-l-4 [&_blockquote]:border-slate-300 [&_blockquote]:pl-4 [&_blockquote]:italic
                                        [&_a]:text-brand [&_a]:underline
                                        [&_img]:mt-5 [&_img]:rounded-2xl
                                        [&_table]:mt-5 [&_table]:w-full [&_table]:border-collapse
                                        [&_th]:border [&_th]:border-slate-300 [&_th]:px-3 [&_th]:py-2 [&_th]:text-left
                                        [&_td]:border [&_td]:border-slate-300 [&_td]:px-3 [&_td]:py-2">
                                {!! $content !!}
                            </div>
                        @else
                            @php
                                $paragraphs = collect(preg_split('/\r\n|\r|\n/', trim($content)))
                                    ->map(fn ($line) => trim($line))
                                    ->filter(fn ($line) => $line !== '')
                                    ->values();
                            @endphp
                            <div class="mt-8 space-y-5 text-[1.02rem] leading-8 text-slate-700">
                                @forelse ($paragraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @empty
                                    <p>{{ $content }}</p>
                                @endforelse
                            </div>
                        @endif
                    </article>

                    <aside class="space-y-6">
                        @if ($posters->count())
                            <section class="rounded-3xl border border-black/5 bg-white p-5 shadow-[0_24px_60px_-45px_rgba(15,23,42,0.5)]">
                                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Posters</h2>
                                <div class="mt-4 space-y-4">
                                    @foreach ($posters as $poster)
                                        <article class="overflow-hidden rounded-2xl border border-black/5 bg-slate-50">
                                            @if ($poster->link_url)
                                                <a href="{{ $poster->link_url }}"
                                                   @if ($poster->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                                   class="block">
                                                    <img src="{{ $poster->image_url }}"
                                                         alt="{{ $poster->alt_text ?: ($poster->title ?: 'Poster') }}"
                                                         class="h-full w-full object-cover">
                                                </a>
                                            @else
                                                <img src="{{ $poster->image_url }}"
                                                     alt="{{ $poster->alt_text ?: ($poster->title ?: 'Poster') }}"
                                                     class="h-full w-full object-cover">
                                            @endif
                                            @if ($poster->title)
                                                <div class="px-3 py-2 text-xs font-semibold">{{ $poster->title }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="rounded-3xl border border-black/5 bg-white p-5 shadow-[0_24px_60px_-45px_rgba(15,23,42,0.5)]">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Alternative Blogs</h2>
                            <div class="mt-4 space-y-4">
                                @forelse ($alternativeBlogs as $alternativeBlog)
                                    <article class="rounded-xl border border-black/5 p-3">
                                        @if ($alternativeBlog->category)
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                {{ $alternativeBlog->category->name }}
                                            </p>
                                        @endif
                                        <h3 class="mt-1 text-sm font-semibold leading-snug">
                                            <a href="{{ route('blogs.show', $alternativeBlog) }}" class="hover:text-brand">
                                                {{ $alternativeBlog->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ ($alternativeBlog->published_at ?? $alternativeBlog->created_at)?->format('M d, Y') }}
                                        </p>
                                    </article>
                                @empty
                                    <p class="text-sm text-slate-500">No alternative blogs yet.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('blogs.index') }}" class="mt-4 inline-flex text-sm font-semibold text-brand hover:underline">
                                View all blogs
                            </a>
                        </section>
                    </aside>
                </div>
            </main>

            @include('partials.site-footer', [
                'globalFooter' => $globalFooter ?? [],
                'globalPurchasePopups' => $globalPurchasePopups ?? [],
                'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
            ])
        </div>
    </body>
</html>
