<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = $globalHeader['favicon_url'] ?? '';
            $faviconUrl = $faviconUrl ?: '/images/logo.svg';
            $canonicalUrl = request()->fullUrl();
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-10">
                <section class="rounded-[28px] border border-black/5 bg-white px-6 py-8 shadow-[0_20px_50px_-35px_rgba(15,23,42,0.45)] md:px-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand">Nai Prints Blog</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight md:text-4xl">Printing, Branding, and Design Stories</h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-600">
                        Read practical guides, creative ideas, and production tips to help your next custom print stand out.
                    </p>
                    @if ($selectedCategory)
                        <p class="mt-4 inline-flex rounded-full border border-brand/20 bg-brand/5 px-3 py-1 text-xs font-semibold text-brand">
                            Category: {{ $selectedCategory->name }}
                        </p>
                    @endif
                </section>

                <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_280px]">
                    <section class="space-y-6">
                        @if ($blogs->count())
                            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($blogs as $blog)
                                    @php
                                        $excerpt = $blog->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($blog->content), 140, '...');
                                        $publishedDate = $blog->published_at ?? $blog->created_at;
                                    @endphp
                                    <article class="overflow-hidden rounded-3xl border border-black/5 bg-white shadow-[0_20px_50px_-35px_rgba(15,23,42,0.45)] transition hover:-translate-y-0.5">
                                        <a href="{{ route('blogs.show', $blog) }}" class="block">
                                            <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                                @if ($blog->cover_image_url)
                                                    <img src="{{ $blog->cover_image_url }}" alt="{{ $blog->title }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 via-white to-slate-200 text-slate-400">
                                                        {{ strtoupper(substr($blog->title, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                        <div class="space-y-3 px-5 py-5">
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                @if ($blog->category)
                                                    <span class="rounded-full border border-black/10 bg-black/5 px-2 py-1">{{ $blog->category->name }}</span>
                                                @endif
                                                <span>{{ $publishedDate?->format('M d, Y') }}</span>
                                            </div>
                                            <h2 class="text-lg font-semibold leading-snug">
                                                <a href="{{ route('blogs.show', $blog) }}" class="hover:text-brand">{{ $blog->title }}</a>
                                            </h2>
                                            <p class="text-sm leading-relaxed text-slate-600">{{ $excerpt }}</p>
                                            <a href="{{ route('blogs.show', $blog) }}" class="inline-flex items-center text-sm font-semibold text-brand hover:underline">
                                                Read full article
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="pt-2">
                                {{ $blogs->links() }}
                            </div>
                        @else
                            <div class="rounded-3xl border border-dashed border-black/15 bg-white px-6 py-10 text-center text-sm text-slate-600">
                                No published blog posts yet.
                            </div>
                        @endif
                    </section>

                    <aside class="space-y-6">
                        <div class="rounded-3xl border border-black/5 bg-white p-5 shadow-[0_20px_50px_-35px_rgba(15,23,42,0.45)]">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Categories</h3>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li>
                                    <a href="{{ route('blogs.index') }}"
                                       class="flex items-center justify-between rounded-xl px-3 py-2 transition {{ !$selectedCategory ? 'bg-brand text-white' : 'hover:bg-slate-100' }}">
                                        <span>All blogs</span>
                                        <span>{{ $categories->sum('published_blogs_count') }}</span>
                                    </a>
                                </li>
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('blogs.index', ['category' => $category->slug]) }}"
                                           class="flex items-center justify-between rounded-xl px-3 py-2 transition {{ (string) ($selectedCategory->id ?? '') === (string) $category->id ? 'bg-brand text-white' : 'hover:bg-slate-100' }}">
                                            <span>{{ $category->name }}</span>
                                            <span>{{ (int) $category->published_blogs_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-black/5 bg-white p-5 shadow-[0_20px_50px_-35px_rgba(15,23,42,0.45)]">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">SEO Ready</h3>
                            <p class="mt-3 text-sm text-slate-600">
                                Each post supports custom meta title, meta description, category organization, and clean slug URLs for better search visibility.
                            </p>
                        </div>
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

