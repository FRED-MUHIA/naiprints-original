<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $canonicalUrl = $selectedCategory
                ? route('blogs.index', ['category' => $selectedCategory->slug])
                : route('blogs.index');
        @endphp
        @include('partials.seo', [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'canonical' => $canonicalUrl,
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-5 lg:px-6">
                @if ($categories->count())
                    <section class="mb-6 flex flex-wrap items-center gap-2">
                        <a href="{{ route('blogs.index') }}"
                           class="rounded-full px-4 py-2 text-xs font-semibold transition {{ !$selectedCategory ? 'bg-brand text-white' : 'bg-slate-200 hover:bg-slate-300' }}">
                            All
                        </a>
                        @foreach ($categories as $category)
                            <a href="{{ route('blogs.index', ['category' => $category->slug]) }}"
                               class="rounded-full px-4 py-2 text-xs font-semibold transition {{ (string) ($selectedCategory->id ?? '') === (string) $category->id ? 'bg-brand text-white' : 'bg-slate-200 hover:bg-slate-300' }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </section>
                @endif

                @if ($blogs->count())
                    <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($blogs as $blog)
                            @php
                                $excerpt = $blog->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($blog->content), 175, '...');
                            @endphp
                            <article class="overflow-hidden rounded-2xl bg-[#f3f4f6]">
                                <a href="{{ route('blogs.show', $blog) }}" class="block">
                                    <div class="aspect-[16/10] overflow-hidden bg-slate-200">
                                        @if ($blog->cover_image_url)
                                            <img src="{{ $blog->cover_image_url }}" alt="{{ $blog->title }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-4xl font-semibold text-slate-400">
                                                {{ strtoupper(substr($blog->title, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                                <div class="space-y-3 px-5 py-5">
                                    <h2 class="text-[1.75rem] font-semibold leading-tight tracking-tight">
                                        <a href="{{ route('blogs.show', $blog) }}" class="hover:text-brand">{{ $blog->title }}</a>
                                    </h2>
                                    <p class="text-sm leading-7 text-slate-700">{{ $excerpt }}</p>
                                    <a href="{{ route('blogs.show', $blog) }}"
                                       class="inline-flex items-center justify-center rounded-full bg-[#003354] px-5 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                                        Read More
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    @if ($blogs->hasMorePages())
                        <div class="mt-8 flex justify-center">
                            <a href="{{ $blogs->nextPageUrl() }}"
                               class="inline-flex items-center justify-center rounded-full bg-[#003354] px-8 py-3 text-sm font-semibold text-white transition hover:opacity-90">
                                Load More
                            </a>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl bg-[#f3f4f6] px-6 py-10 text-center text-sm text-slate-600">
                        No published blog posts yet.
                    </div>
                @endif
            </main>

            @include('partials.site-footer', [
                'globalFooter' => $globalFooter ?? [],
                'globalPurchasePopups' => $globalPurchasePopups ?? [],
                'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
            ])
        </div>
    </body>
</html>
