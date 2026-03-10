@extends('admin.layout')

@section('title', 'Edit Page')

@section('content')
    @php
        $sections = $page->content['sections'] ?? [];
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Page Builder</p>
                    <h2 class="mt-2 text-2xl font-semibold">{{ $page->title }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Edit the sections below. Changes save into the database as JSON.</p>
                </div>
                <a href="{{ route('admin.builder.index') }}" class="admin-btn-outline text-xs uppercase tracking-[0.3em]">
                    Back
                </a>
            </div>

            <form method="POST" action="{{ route('admin.builder.update', $page) }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-sm font-semibold text-slate-700">Page Title</label>
                    <input type="text" name="title" value="{{ $page->title }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                </div>

                @foreach ($sections as $index => $section)
                    @php
                        $type = $section['type'] ?? 'section';
                        $data = $section['data'] ?? [];
                        $typeLabel = ucwords(str_replace('_', ' ', $type));
                    @endphp
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Section {{ $index + 1 }}</p>
                                <h3 class="mt-1 text-lg font-semibold">{{ $typeLabel }}</h3>
                            </div>
                            <span class="admin-status admin-status-draft">{{ $type }}</span>
                        </div>
                        <input type="hidden" name="sections[{{ $index }}][type]" value="{{ $type }}">

                        <div class="mt-4 grid gap-4">
                            @if ($type === 'hero')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][highlight]" value="{{ $data['highlight'] ?? '' }}" placeholder="Highlight" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                <textarea name="sections[{{ $index }}][data][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $data['body'] ?? '' }}</textarea>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][primary][label]" value="{{ $data['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][primary][url]" value="{{ $data['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][secondary][label]" value="{{ $data['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][secondary][url]" value="{{ $data['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $stats = $data['stats'] ?? [];
                                    if (empty($stats)) {
                                        $stats = [
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                        ];
                                    }
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($stats as $statIndex => $stat)
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][stats][{{ $statIndex }}][value]" value="{{ $stat['value'] ?? '' }}" placeholder="Stat value" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][stats][{{ $statIndex }}][label]" value="{{ $stat['label'] ?? '' }}" placeholder="Stat label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($type === 'tiles')
                                @php
                                    $tiles = $data['tiles'] ?? [];
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($tiles as $tileIndex => $tile)
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][kicker]" value="{{ $tile['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][title]" value="{{ $tile['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][theme]" value="{{ $tile['theme'] ?? '' }}" placeholder="Theme class" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][background_image]" value="{{ $tile['background_image'] ?? '' }}" placeholder="Background image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="file"
                                                   name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][background_image_file]"
                                                   accept="image/*"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                        @if ($tileIndex === 1)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text"
                                                       name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][video_url]"
                                                       value="{{ $tile['video_url'] ?? '' }}"
                                                       placeholder="Video URL (MP4/WebM/Ogg)"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="file"
                                                       name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][video_file]"
                                                       accept="video/mp4,video/webm,video/ogg"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <div class="text-xs text-slate-500 flex items-center sm:col-span-2">
                                                    Paste a direct video link (MP4/WebM/Ogg) or upload a file. Uploaded files will override the URL.
                                                </div>
                                            </div>
                                        @else
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][background_image_alt]" value="{{ $tile['background_image_alt'] ?? '' }}" placeholder="Flip image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="file"
                                                       name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][background_image_alt_file]"
                                                       accept="image/*"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endif
                                        @if (!empty($tile['background_image']))
                                            <div class="text-xs text-slate-500">
                                                Current: <a href="{{ $tile['background_image'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                            </div>
                                        @endif
                                        @if (!empty($tile['background_image_alt']))
                                            <div class="text-xs text-slate-500">
                                                Flip: <a href="{{ $tile['background_image_alt'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                            </div>
                                        @endif
                                        @if (!empty($tile['video_url']))
                                            <div class="text-xs text-slate-500">
                                                Video: <a href="{{ $tile['video_url'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View video</a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif ($type === 'category_carousel')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][view_all_label]" value="{{ $data['view_all_label'] ?? '' }}" placeholder="View all label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][view_all_url]" value="{{ $data['view_all_url'] ?? '' }}" placeholder="View all URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $items = $data['items'] ?? [];
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($items as $itemIndex => $item)
                                        <div class="grid gap-3 sm:grid-cols-5">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][icon]" value="{{ $item['icon'] ?? '' }}" placeholder="Icon" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][title]" value="{{ $item['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="number" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][count]" value="{{ $item['count'] ?? 0 }}" placeholder="Count" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][image_url]" value="{{ $item['image_url'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="Link URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($type === 'collection_showcase')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Badge label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][hero_note]" value="{{ $data['hero_note'] ?? '' }}" placeholder="Hero note" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][badge_image]" value="{{ $data['badge_image'] ?? '' }}" placeholder="Badge image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][badge_alt]" value="{{ $data['badge_alt'] ?? '' }}" placeholder="Badge image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][hero_image]" value="{{ $data['hero_image'] ?? '' }}" placeholder="Hero image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][hero_alt]" value="{{ $data['hero_alt'] ?? '' }}" placeholder="Hero image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $items = $data['items'] ?? [];
                                    if (empty($items)) {
                                        $items = [
                                            ['label' => 'Birthdays', 'image_url' => '', 'url' => '/shop'],
                                            ['label' => 'Corporate Tshirts', 'image_url' => '', 'url' => '/shop'],
                                            ['label' => 'Couple', 'image_url' => '', 'url' => '/shop'],
                                        ];
                                    }
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($items as $itemIndex => $item)
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][label]" value="{{ $item['label'] ?? '' }}" placeholder="Button label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][image_url]" value="{{ $item['image_url'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][items][{{ $itemIndex }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="Link URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($type === 'best_sellers')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][meta_left]" value="{{ $data['meta_left'] ?? '' }}" placeholder="Meta left" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][meta_right]" value="{{ $data['meta_right'] ?? '' }}" placeholder="Meta right" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                            @elseif ($type === 'gift_finder')
                                @php
                                    $giftFinderOptions = $data['select_options'] ?? [];
                                    if (!is_array($giftFinderOptions)) {
                                        $giftFinderOptions = array_filter(array_map('trim', explode(',', (string) $giftFinderOptions)));
                                    }
                                    if (empty($giftFinderOptions)) {
                                        $giftFinderOptions = ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                                    }
                                    $giftFinderTitle = $data['title'] ?? 'Need to Find a Gift?';
                                    $giftFinderLeadBefore = $data['lead_before'] ?? 'Find the perfect';
                                    $giftFinderLeadAfter = $data['lead_after'] ?? 'for your favorite:';
                                    $giftFinderCtaLabel = $data['cta_label'] ?? 'View more designs';
                                    $giftFinderCtaUrl = $data['cta_url'] ?? '/shop';
                                    $giftFinderInterval = $data['carousel_interval'] ?? 5;
                                    $giftFinderCategoryId = $data['product_category_id'] ?? '';
                                    $giftFinderCategoryId = is_numeric($giftFinderCategoryId) ? (int) $giftFinderCategoryId : '';
                                @endphp
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $giftFinderTitle }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="number" name="sections[{{ $index }}][data][carousel_interval]" value="{{ $giftFinderInterval }}" min="1" placeholder="Carousel interval (seconds)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][lead_before]" value="{{ $giftFinderLeadBefore }}" placeholder="Lead text (before select)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][lead_after]" value="{{ $giftFinderLeadAfter }}" placeholder="Lead text (after select)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][cta_label]" value="{{ $giftFinderCtaLabel }}" placeholder="CTA label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][cta_url]" value="{{ $giftFinderCtaUrl }}" placeholder="CTA URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product category</label>
                                    <select name="sections[{{ $index }}][data][product_category_id]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <option value="">Default (Design)</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) $giftFinderCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid gap-3">
                                    @foreach ($giftFinderOptions as $optionIndex => $option)
                                        <input type="text" name="sections[{{ $index }}][data][select_options][{{ $optionIndex }}]" value="{{ $option }}" placeholder="Select option" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    @endforeach
                                </div>
                            @elseif ($type === 'how_it_works')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $steps = $data['steps'] ?? [];
                                    $giftFinder = $data['gift_finder'] ?? [];
                                    $giftFinderOptions = $giftFinder['select_options'] ?? [];
                                    if (!is_array($giftFinderOptions)) {
                                        $giftFinderOptions = array_filter(array_map('trim', explode(',', (string) $giftFinderOptions)));
                                    }
                                    if (empty($giftFinderOptions)) {
                                        $giftFinderOptions = ['T-Shirt', 'Hoodie', 'Polo', 'Cap'];
                                    }
                                    $giftFinderTitle = $giftFinder['title'] ?? 'Need to Find a Gift?';
                                    $giftFinderLeadBefore = $giftFinder['lead_before'] ?? 'Find the perfect';
                                    $giftFinderLeadAfter = $giftFinder['lead_after'] ?? 'for your favorite:';
                                    $giftFinderCtaLabel = $giftFinder['cta_label'] ?? 'View more designs';
                                    $giftFinderCtaUrl = $giftFinder['cta_url'] ?? '/shop';
                                    $giftFinderInterval = $giftFinder['carousel_interval'] ?? 5;
                                    $giftFinderCategoryId = $giftFinder['product_category_id'] ?? '';
                                    $giftFinderCategoryId = is_numeric($giftFinderCategoryId) ? (int) $giftFinderCategoryId : '';
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($steps as $stepIndex => $step)
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][body]" value="{{ $step['body'] ?? '' }}" placeholder="Step description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Gift Finder</p>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][title]" value="{{ $giftFinderTitle }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="number" name="sections[{{ $index }}][data][gift_finder][carousel_interval]" value="{{ $giftFinderInterval }}" min="1" placeholder="Carousel interval (seconds)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][lead_before]" value="{{ $giftFinderLeadBefore }}" placeholder="Lead text (before select)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][lead_after]" value="{{ $giftFinderLeadAfter }}" placeholder="Lead text (after select)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][cta_label]" value="{{ $giftFinderCtaLabel }}" placeholder="CTA label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][cta_url]" value="{{ $giftFinderCtaUrl }}" placeholder="CTA URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                        <div class="mt-3">
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product category</label>
                                            <select name="sections[{{ $index }}][data][gift_finder][product_category_id]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <option value="">Default (Design)</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((string) $giftFinderCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($giftFinderOptions as $optionIndex => $option)
                                            <input type="text" name="sections[{{ $index }}][data][gift_finder][select_options][{{ $optionIndex }}]" value="{{ $option }}" placeholder="Select option" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($type === 'embroidery_page')
                                @php
                                    $hero = $data['hero'] ?? [];
                                    $heroStats = $hero['stats'] ?? [];
                                    if (empty($heroStats)) {
                                        $heroStats = [
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                        ];
                                    }

                                    $infoCards = $data['info_cards'] ?? [];
                                    if (empty($infoCards)) {
                                        $infoCards = [
                                            ['kicker' => '', 'title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                            ['kicker' => '', 'title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                        ];
                                    }

                                    $services = $data['services'] ?? [];
                                    $serviceItems = $services['items'] ?? [];
                                    if (empty($serviceItems)) {
                                        $serviceItems = [
                                            ['title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                            ['title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                            ['title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                            ['title' => '', 'body' => '', 'image_url' => '', 'image_alt' => ''],
                                        ];
                                    }

                                    $impact = $data['impact'] ?? [];
                                    $impactBullets = $impact['bullets'] ?? [];
                                    if (empty($impactBullets)) {
                                        $impactBullets = ['', '', ''];
                                    }

                                    $process = $data['process'] ?? [];
                                    $processSteps = $process['steps'] ?? [];
                                    if (empty($processSteps)) {
                                        $processSteps = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }

                                    $portfolio = $data['portfolio'] ?? [];
                                    $portfolioItems = $portfolio['items'] ?? [];
                                    if (empty($portfolioItems)) {
                                        $portfolioItems = [
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                        ];
                                    }

                                    $cta = $data['cta'] ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Hero</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][kicker]" value="{{ $hero['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][highlight]" value="{{ $hero['highlight'] ?? '' }}" placeholder="Highlight" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][hero][title]" value="{{ $hero['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <textarea name="sections[{{ $index }}][data][hero][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $hero['body'] ?? '' }}</textarea>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][primary][label]" value="{{ $hero['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][primary][url]" value="{{ $hero['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][secondary][label]" value="{{ $hero['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][secondary][url]" value="{{ $hero['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="grid gap-3">
                                        @foreach ($heroStats as $statIndex => $stat)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][hero][stats][{{ $statIndex }}][value]" value="{{ $stat['value'] ?? '' }}" placeholder="Stat value" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][hero][stats][{{ $statIndex }}][label]" value="{{ $stat['label'] ?? '' }}" placeholder="Stat label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Info Cards</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($infoCards as $cardIndex => $card)
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][info_cards][{{ $cardIndex }}][kicker]" value="{{ $card['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][info_cards][{{ $cardIndex }}][title]" value="{{ $card['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                <textarea name="sections[{{ $index }}][data][info_cards][{{ $cardIndex }}][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $card['body'] ?? '' }}</textarea>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][info_cards][{{ $cardIndex }}][image_url]" value="{{ $card['image_url'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][info_cards][{{ $cardIndex }}][image_alt]" value="{{ $card['image_alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Services</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][services][kicker]" value="{{ $services['kicker'] ?? '' }}" placeholder="Section kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][services][title]" value="{{ $services['title'] ?? '' }}" placeholder="Section title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($serviceItems as $itemIndex => $item)
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <input type="text" name="sections[{{ $index }}][data][services][items][{{ $itemIndex }}][title]" value="{{ $item['title'] ?? '' }}" placeholder="Service title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <textarea name="sections[{{ $index }}][data][services][items][{{ $itemIndex }}][body]" rows="3" placeholder="Service description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $item['body'] ?? '' }}</textarea>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][services][items][{{ $itemIndex }}][image_url]" value="{{ $item['image_url'] ?? '' }}" placeholder="Circle image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][services][items][{{ $itemIndex }}][image_alt]" value="{{ $item['image_alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Impact</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][impact][kicker]" value="{{ $impact['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][impact][title]" value="{{ $impact['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <textarea name="sections[{{ $index }}][data][impact][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $impact['body'] ?? '' }}</textarea>
                                    <div class="grid gap-3">
                                        @foreach ($impactBullets as $bulletIndex => $bullet)
                                            <input type="text" name="sections[{{ $index }}][data][impact][bullets][{{ $bulletIndex }}]" value="{{ $bullet }}" placeholder="Bullet text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Process</p>
                                    <input type="text" name="sections[{{ $index }}][data][process][kicker]" value="{{ $process['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <div class="grid gap-3">
                                        @foreach ($processSteps as $stepIndex => $step)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][process][steps][{{ $stepIndex }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][process][steps][{{ $stepIndex }}][body]" value="{{ $step['body'] ?? '' }}" placeholder="Step description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Portfolio</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][portfolio][kicker]" value="{{ $portfolio['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][portfolio][title]" value="{{ $portfolio['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach ($portfolioItems as $itemIndex => $item)
                                            <div class="grid gap-2">
                                                <input type="text" name="sections[{{ $index }}][data][portfolio][items][{{ $itemIndex }}][image_url]" value="{{ $item['image_url'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][portfolio][items][{{ $itemIndex }}][image_alt]" value="{{ $item['image_alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Call To Action</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][kicker]" value="{{ $cta['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][title]" value="{{ $cta['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <textarea name="sections[{{ $index }}][data][cta][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $cta['body'] ?? '' }}</textarea>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][label]" value="{{ $cta['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][url]" value="{{ $cta['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][label]" value="{{ $cta['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][url]" value="{{ $cta['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            @elseif ($type === 'cta')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <textarea name="sections[{{ $index }}][data][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $data['body'] ?? '' }}</textarea>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][primary][label]" value="{{ $data['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][primary][url]" value="{{ $data['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][secondary][label]" value="{{ $data['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][secondary][url]" value="{{ $data['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $stats = $data['stats'] ?? [];
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($stats as $statIndex => $stat)
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <input type="text" name="sections[{{ $index }}][data][stats][{{ $statIndex }}][value]" value="{{ $stat['value'] ?? '' }}" placeholder="Stat value" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][stats][{{ $statIndex }}][label]" value="{{ $stat['label'] ?? '' }}" placeholder="Stat label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($type === 'design_studio')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <textarea name="sections[{{ $index }}][data][body]" rows="3" placeholder="Body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $data['body'] ?? '' }}</textarea>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][background_image]" value="{{ $data['background_image'] ?? '' }}" placeholder="Background image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="file"
                                           name="sections[{{ $index }}][data][background_image_file]"
                                           accept="image/*"
                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @if (!empty($data['background_image']))
                                    <div class="text-xs text-slate-500">
                                        Current: <a href="{{ $data['background_image'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                    </div>
                                @endif
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][button][label]" value="{{ $data['button']['label'] ?? '' }}" placeholder="Button label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][button][url]" value="{{ $data['button']['url'] ?? '' }}" placeholder="Button URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                            @elseif ($type === 'corporate_page')
                                @php
                                    $hero = $data['hero'] ?? [];
                                    $heroStats = $hero['stats'] ?? [];
                                    if (empty($heroStats)) {
                                        $heroStats = [
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                            ['value' => '', 'label' => ''],
                                        ];
                                    }
                                    $highlights = $data['highlights'] ?? [];
                                    if (empty($highlights)) {
                                        $highlights = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }
                                    $process = $data['process'] ?? [];
                                    $processSteps = $process['steps'] ?? [];
                                    if (empty($processSteps)) {
                                        $processSteps = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }
                                    $logos = $data['logos'] ?? [];
                                    $logoItems = $logos['items'] ?? [];
                                    if (empty($logoItems)) {
                                        $logoItems = ['', '', '', '', '', ''];
                                    }
                                    $cta = $data['cta'] ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Hero</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][kicker]" value="{{ $hero['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][kicker_note]" value="{{ $hero['kicker_note'] ?? '' }}" placeholder="Kicker note" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][hero][title]" value="{{ $hero['title'] ?? '' }}" placeholder="Title" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <textarea name="sections[{{ $index }}][data][hero][body]" rows="3" placeholder="Body" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $hero['body'] ?? '' }}</textarea>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][image]" value="{{ $hero['image'] ?? '' }}" placeholder="Hero image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file"
                                               name="sections[{{ $index }}][data][hero][image_file]"
                                               accept="image/*"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][hero][image_alt]" value="{{ $hero['image_alt'] ?? '' }}" placeholder="Hero image alt text" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    @if (!empty($hero['image']))
                                        <div class="mt-2 text-xs text-slate-500">
                                            Current: <a href="{{ $hero['image'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                        </div>
                                    @endif
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][primary][label]" value="{{ $hero['primary']['label'] ?? '' }}" placeholder="Primary button label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][primary][url]" value="{{ $hero['primary']['url'] ?? '' }}" placeholder="Primary button URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][secondary][label]" value="{{ $hero['secondary']['label'] ?? '' }}" placeholder="Secondary button label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][secondary][url]" value="{{ $hero['secondary']['url'] ?? '' }}" placeholder="Secondary button URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($heroStats as $statIndex => $stat)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][hero][stats][{{ $statIndex }}][value]" value="{{ $stat['value'] ?? '' }}" placeholder="Stat value" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][hero][stats][{{ $statIndex }}][label]" value="{{ $stat['label'] ?? '' }}" placeholder="Stat label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Highlights</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($highlights as $highlightIndex => $highlight)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][highlights][{{ $highlightIndex }}][title]" value="{{ $highlight['title'] ?? '' }}" placeholder="Highlight title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][highlights][{{ $highlightIndex }}][body]" value="{{ $highlight['body'] ?? '' }}" placeholder="Highlight body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Process</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][process][kicker]" value="{{ $process['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][process][title]" value="{{ $process['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($processSteps as $stepIndex => $step)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][process][steps][{{ $stepIndex }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][process][steps][{{ $stepIndex }}][body]" value="{{ $step['body'] ?? '' }}" placeholder="Step body" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Logos</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][logos][kicker]" value="{{ $logos['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][logos][title]" value="{{ $logos['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach ($logoItems as $logoIndex => $logoItem)
                                            <input type="text" name="sections[{{ $index }}][data][logos][items][{{ $logoIndex }}]" value="{{ $logoItem }}" placeholder="Logo item" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">CTA</p>
                                    <input type="text" name="sections[{{ $index }}][data][cta][title]" value="{{ $cta['title'] ?? '' }}" placeholder="CTA title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <textarea name="sections[{{ $index }}][data][cta][body]" rows="3" placeholder="CTA body" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $cta['body'] ?? '' }}</textarea>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][label]" value="{{ $cta['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][url]" value="{{ $cta['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][label]" value="{{ $cta['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][url]" value="{{ $cta['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            @elseif ($type === 'shop_header')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][subtitle]" value="{{ $data['subtitle'] ?? '' }}" placeholder="Subtitle" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                            @elseif ($type === 'designer_page')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][brand_name]" value="{{ $data['brand_name'] ?? '' }}" placeholder="Brand name" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][brand_subtitle]" value="{{ $data['brand_subtitle'] ?? '' }}" placeholder="Brand subtitle" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][topbar_title]" value="{{ $data['topbar_title'] ?? '' }}" placeholder="Top bar title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][user_initials]" value="{{ $data['user_initials'] ?? '' }}" placeholder="User initials" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][add_to_cart_label]" value="{{ $data['add_to_cart_label'] ?? '' }}" placeholder="Add to cart label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][add_to_cart_url]" value="{{ $data['add_to_cart_url'] ?? '' }}" placeholder="Add to cart URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][back_to_shop_label]" value="{{ $data['back_to_shop_label'] ?? '' }}" placeholder="Back to shop label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][back_to_shop_url]" value="{{ $data['back_to_shop_url'] ?? '' }}" placeholder="Back to shop URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][share_label]" value="{{ $data['share_label'] ?? '' }}" placeholder="Share label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $topMenu = $data['top_menu'] ?? ['File', 'Resize', 'Upload'];
                                    if (empty($topMenu)) {
                                        $topMenu = ['File', 'Resize', 'Upload'];
                                    }
                                    $leftRail = $data['left_rail'] ?? [];
                                    if (empty($leftRail)) {
                                        $leftRail = [
                                            ['label' => 'Tools', 'icon' => '🧰'],
                                            ['label' => 'Projects', 'icon' => '📁'],
                                            ['label' => 'Apps', 'icon' => '🧩'],
                                            ['label' => 'Magic', 'icon' => '✨'],
                                            ['label' => 'QR', 'icon' => '🔳'],
                                            ['label' => 'Voice', 'icon' => '🔊'],
                                            ['label' => 'Tracer', 'icon' => '🧪'],
                                            ['label' => 'Mockups', 'icon' => '🖼️'],
                                        ];
                                    }
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Top Menu Items</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($topMenu as $menuIndex => $menuLabel)
                                            <input type="text" name="sections[{{ $index }}][data][top_menu][{{ $menuIndex }}]" value="{{ $menuLabel }}" placeholder="Menu label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Left Rail Items</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($leftRail as $railIndex => $railItem)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][left_rail][{{ $railIndex }}][icon]" value="{{ $railItem['icon'] ?? '' }}" placeholder="Icon" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][left_rail][{{ $railIndex }}][label]" value="{{ $railItem['label'] ?? '' }}" placeholder="Label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php
                                    $mockups = $data['mockups'] ?? [];
                                    $productColors = $data['product_colors'] ?? [];
                                    $productItems = $data['products'] ?? [];
                                    $mockupProducts = [
                                        'tshirt' => ['front', 'back'],
                                        'polo' => ['front', 'back'],
                                        'hoodie' => ['front', 'back'],
                                        'cap' => ['front'],
                                        'mug' => ['front'],
                                        'pillow' => ['front', 'back'],
                                    ];
                                    $printAreas = $data['print_areas'] ?? [];
                                    $printAreaDefaults = [
                                        'tshirt' => [
                                            'front' => ['x' => 157, 'y' => 175, 'w' => 260, 'h' => 280],
                                            'back' => ['x' => 145, 'y' => 146, 'w' => 260, 'h' => 300],
                                        ],
                                        'polo' => [
                                            'front' => ['x' => 185, 'y' => 240, 'w' => 230, 'h' => 260],
                                            'back' => ['x' => 185, 'y' => 230, 'w' => 230, 'h' => 280],
                                        ],
                                        'hoodie' => [
                                            'front' => ['x' => 180, 'y' => 260, 'w' => 240, 'h' => 260],
                                            'back' => ['x' => 180, 'y' => 250, 'w' => 240, 'h' => 280],
                                        ],
                                        'cap' => [
                                            'front' => ['x' => 200, 'y' => 310, 'w' => 200, 'h' => 130],
                                        ],
                                        'mug' => [
                                            'front' => ['x' => 190, 'y' => 240, 'w' => 190, 'h' => 200],
                                        ],
                                        'pillow' => [
                                            'front' => ['x' => 180, 'y' => 230, 'w' => 240, 'h' => 240],
                                            'back' => ['x' => 180, 'y' => 230, 'w' => 240, 'h' => 240],
                                        ],
                                    ];
                                    $productDefaults = [
                                        'tshirt' => ['name' => 'T-Shirt', 'icon' => '👕'],
                                        'polo' => ['name' => 'Polo Shirt', 'icon' => '🧥'],
                                        'hoodie' => ['name' => 'Hoodie', 'icon' => '🧢'],
                                        'cap' => ['name' => 'Cap', 'icon' => '🧢'],
                                        'mug' => ['name' => 'Cup / Mug', 'icon' => '☕'],
                                        'pillow' => ['name' => 'Pillow', 'icon' => '🛏️'],
                                    ];
                                    $mockupColors = [
                                        'tshirt' => [
                                            ['name' => 'White', 'value' => '#f8fafc'],
                                            ['name' => 'Black', 'value' => '#0f172a'],
                                            ['name' => 'Navy', 'value' => '#1e293b'],
                                            ['name' => 'Red', 'value' => '#ef4444'],
                                        ],
                                        'polo' => [
                                            ['name' => 'Sand', 'value' => '#f3e8e0'],
                                            ['name' => 'Forest', 'value' => '#14532d'],
                                            ['name' => 'Ink', 'value' => '#0f172a'],
                                        ],
                                        'hoodie' => [
                                            ['name' => 'Sky', 'value' => '#93c5fd'],
                                            ['name' => 'Charcoal', 'value' => '#111827'],
                                            ['name' => 'Cream', 'value' => '#fef3c7'],
                                        ],
                                        'cap' => [
                                            ['name' => 'Black', 'value' => '#0b1026'],
                                            ['name' => 'Stone', 'value' => '#e2e8f0'],
                                            ['name' => 'Olive', 'value' => '#4d7c0f'],
                                        ],
                                        'mug' => [
                                            ['name' => 'White', 'value' => '#f8fafc'],
                                            ['name' => 'Slate', 'value' => '#334155'],
                                            ['name' => 'Amber', 'value' => '#f59e0b'],
                                        ],
                                        'pillow' => [
                                            ['name' => 'Ivory', 'value' => '#fffbeb'],
                                            ['name' => 'Rose', 'value' => '#fbcfe8'],
                                            ['name' => 'Ink', 'value' => '#1f2937'],
                                        ],
                                    ];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Tiles</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($productDefaults as $productKey => $productData)
                                            @php
                                                $productName = $productItems[$productKey]['name'] ?? $productData['name'];
                                                $productIcon = $productItems[$productKey]['icon'] ?? $productData['icon'];
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">{{ ucfirst($productKey) }}</p>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][products][{{ $productKey }}][name]"
                                                           value="{{ $productName }}"
                                                           placeholder="Display name"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][products][{{ $productKey }}][icon]"
                                                           value="{{ $productIcon }}"
                                                           placeholder="Icon emoji or image URL"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Colors</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($mockupProducts as $productKey => $views)
                                            @php
                                                $productLabel = ucfirst($productKey);
                                                $colorDefaults = $mockupColors[$productKey] ?? [];
                                                $colorPlaceholder = collect($colorDefaults)
                                                    ->map(fn ($color) => "{$color['name']}|{$color['value']}")
                                                    ->implode("\n");
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">{{ $productLabel }}</p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    One color per line. Accepted formats: "Name|#hex", "Name:#hex", or "#hex Name".
                                                </p>
                                                <textarea
                                                    name="sections[{{ $index }}][data][product_colors][{{ $productKey }}]"
                                                    rows="4"
                                                    placeholder="{{ $colorPlaceholder }}"
                                                    class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $productColors[$productKey] ?? '' }}</textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Mockups</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($mockupProducts as $productKey => $views)
                                            @php
                                                $productLabel = ucfirst($productKey);
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">{{ $productLabel }}</p>
                                                <div class="mt-3 grid gap-3">
                                                    @foreach ($views as $viewKey)
                                                        @php
                                                            $viewMockupRaw = $mockups[$productKey][$viewKey] ?? '';
                                                            $viewMockup = is_array($viewMockupRaw) ? $viewMockupRaw : [];
                                                            $defaultMockup = is_array($viewMockupRaw)
                                                                ? ($viewMockup['default'] ?? '')
                                                                : (is_string($viewMockupRaw) ? $viewMockupRaw : '');
                                                            $colorMockups = is_array($viewMockup['colors'] ?? null)
                                                                ? $viewMockup['colors']
                                                                : [];
                                                            $viewColors = $mockupColors[$productKey] ?? [];
                                                        @endphp
                                                        <div class="rounded-lg border border-slate-200 p-3">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ ucfirst($viewKey) }} default</p>
                                                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                                                <input type="text"
                                                                       name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][default]"
                                                                       value="{{ $defaultMockup }}"
                                                                       placeholder="{{ ucfirst($viewKey) }} image URL"
                                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                                <input type="file"
                                                                       name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][default_file]"
                                                                       accept="image/*"
                                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                            </div>
                                                            @if (!empty($defaultMockup))
                                                                <div class="mt-2 text-xs text-slate-500">
                                                                    Current: <a href="{{ $defaultMockup }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if (!empty($viewColors))
                                                            <div class="rounded-lg border border-slate-200 p-3">
                                                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ ucfirst($viewKey) }} color variants</p>
                                                                <div class="mt-2 grid gap-3">
                                                                    @foreach ($viewColors as $colorOption)
                                                                        @php
                                                                            $colorKey = strtolower(ltrim($colorOption['value'], '#'));
                                                                            $colorUrl = $colorMockups[$colorKey] ?? '';
                                                                        @endphp
                                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                                            <input type="text"
                                                                                   name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][colors][{{ $colorKey }}]"
                                                                                   value="{{ $colorUrl }}"
                                                                                   placeholder="{{ $colorOption['name'] }} image URL"
                                                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                                            <input type="file"
                                                                                   name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][colors][{{ $colorKey }}_file]"
                                                                                   accept="image/*"
                                                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                                        </div>
                                                                        @if (!empty($colorUrl))
                                                                            <div class="text-xs text-slate-500">
                                                                                {{ $colorOption['name'] }}: <a href="{{ $colorUrl }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($type === 'mockup_page')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][brand_name]" value="{{ $data['brand_name'] ?? '' }}" placeholder="Brand name" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][brand_mark]" value="{{ $data['brand_mark'] ?? '' }}" placeholder="Brand mark (e.g. NP)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][brand_logo]" value="{{ $data['brand_logo'] ?? '' }}" placeholder="Brand logo URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="file"
                                           name="sections[{{ $index }}][data][brand_logo_file]"
                                           accept="image/*"
                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @if (!empty($data['brand_logo']))
                                    <div class="text-xs text-slate-500">
                                        Current: <a href="{{ $data['brand_logo'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View logo</a>
                                    </div>
                                @endif
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][topbar_title]" value="{{ $data['topbar_title'] ?? '' }}" placeholder="Top bar title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][save_label]" value="{{ $data['save_label'] ?? '' }}" placeholder="Save design label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][file_title]" value="{{ $data['file_title'] ?? '' }}" placeholder="File menu title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][file_size]" value="{{ $data['file_size'] ?? '' }}" placeholder="File menu size" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][add_to_cart_label]" value="{{ $data['add_to_cart_label'] ?? '' }}" placeholder="Add to cart label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][add_to_cart_url]" value="{{ $data['add_to_cart_url'] ?? '' }}" placeholder="Add to cart URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][back_to_shop_label]" value="{{ $data['back_to_shop_label'] ?? '' }}" placeholder="Back to shop label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][back_to_shop_url]" value="{{ $data['back_to_shop_url'] ?? '' }}" placeholder="Back to shop URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][share_label]" value="{{ $data['share_label'] ?? '' }}" placeholder="Share label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                @php
                                    $topMenu = $data['top_menu'] ?? ['File', 'Resize', 'Editing'];
                                    if (empty($topMenu)) {
                                        $topMenu = ['File', 'Resize', 'Editing'];
                                    }
                                    $railDefaults = [
                                        'mockups' => 'Products',
                                        'templates' => 'Templates',
                                        'apps' => 'Apps',
                                        'uploads' => 'Uploads',
                                        'edit' => 'Edit',
                                        'qr' => 'QR Code',
                                        'voice' => 'AI Voice',
                                    ];
                                    $railLabels = $data['rail_labels'] ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Top Menu Items</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($topMenu as $menuIndex => $menuLabel)
                                            <input type="text" name="sections[{{ $index }}][data][top_menu][{{ $menuIndex }}]" value="{{ $menuLabel }}" placeholder="Menu label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Left Rail Labels</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach ($railDefaults as $railKey => $railLabel)
                                            <input type="text"
                                                   name="sections[{{ $index }}][data][rail_labels][{{ $railKey }}]"
                                                   value="{{ $railLabels[$railKey] ?? $railLabel }}"
                                                   placeholder="{{ $railLabel }} label"
                                                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        @endforeach
                                    </div>
                                </div>
                                @php
                                    $templateResults = $data['templates']['all_results'] ?? [];
                                    if (!is_array($templateResults)) {
                                        $templateResults = [];
                                    }
                                    $templateResults = collect($templateResults)
                                        ->map(function ($item) {
                                            if (is_string($item)) {
                                                return ['label' => '', 'image' => $item];
                                            }
                                            return is_array($item) ? $item : ['label' => '', 'image' => ''];
                                        })
                                        ->values()
                                        ->all();
                                    if (count($templateResults) < 4) {
                                        $templateResults = array_merge(
                                            $templateResults,
                                            array_fill(0, 4 - count($templateResults), ['label' => '', 'image' => ''])
                                        );
                                    }
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Templates</p>
                                    <p class="mt-2 text-xs text-slate-500">Upload template images to show in the "All results" grid.</p>
                                    <div class="mt-3 grid gap-4"
                                         data-template-list
                                         data-template-section="{{ $index }}"
                                         data-template-next-index="{{ count($templateResults) }}">
                                        @foreach ($templateResults as $templateIndex => $template)
                                            @php
                                                $templateLabel = $template['label'] ?? '';
                                                $templateImage = $template['image'] ?? '';
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">Template {{ $templateIndex + 1 }}</p>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][templates][all_results][{{ $templateIndex }}][label]"
                                                           value="{{ $templateLabel }}"
                                                           placeholder="Template label"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][templates][all_results][{{ $templateIndex }}][image]"
                                                           value="{{ $templateImage }}"
                                                           placeholder="Template image URL"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="file"
                                                           name="sections[{{ $index }}][data][templates][all_results][{{ $templateIndex }}][image_file]"
                                                           accept="image/*"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                @if (!empty($templateImage))
                                                    <div class="mt-2 text-xs text-slate-500">
                                                        Current: <a href="{{ $templateImage }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button"
                                            class="admin-btn-outline text-xs uppercase tracking-[0.3em]"
                                            data-template-add>
                                        Add template
                                    </button>
                                </div>
                                @php
                                    $mockupProducts = [
                                        'tshirt' => 'Tshirt',
                                        'hoodie' => 'Hoodies',
                                        'polo' => 'Polo Tshirt',
                                        'cap' => 'Caps',
                                        'mug' => 'Mug',
                                        'pillow' => 'Pillow',
                                        'totebag' => 'Totebags',
                                    ];
                                    $mockupViews = ['front', 'back'];
                                    $mockups = is_array($data['mockups'] ?? null) ? $data['mockups'] : [];
                                    $productColors = is_array($data['product_colors'] ?? null) ? $data['product_colors'] : [];
                                    $defaultColors = [
                                        'tshirt' => [
                                            ['name' => 'Classic White', 'value' => '#f8fafc'],
                                            ['name' => 'Ink Black', 'value' => '#111827'],
                                            ['name' => 'Sand', 'value' => '#f5e9d8'],
                                        ],
                                        'hoodie' => [
                                            ['name' => 'Heather', 'value' => '#e5e7eb'],
                                            ['name' => 'Charcoal', 'value' => '#1f2937'],
                                            ['name' => 'Storm', 'value' => '#334155'],
                                        ],
                                        'polo' => [
                                            ['name' => 'Ivory', 'value' => '#f9fafb'],
                                            ['name' => 'Navy', 'value' => '#1e293b'],
                                            ['name' => 'Olive', 'value' => '#4b5d4a'],
                                        ],
                                        'cap' => [
                                            ['name' => 'Classic', 'value' => '#f9fafb'],
                                            ['name' => 'Midnight', 'value' => '#0f172a'],
                                            ['name' => 'Clay', 'value' => '#d9b99b'],
                                        ],
                                        'mug' => [
                                            ['name' => 'Glossy', 'value' => '#f9fafb'],
                                            ['name' => 'Graphite', 'value' => '#334155'],
                                            ['name' => 'Cream', 'value' => '#f3e9d9'],
                                        ],
                                        'pillow' => [
                                            ['name' => 'Soft White', 'value' => '#f9fafb'],
                                            ['name' => 'Stone', 'value' => '#cbd5f5'],
                                            ['name' => 'Rose', 'value' => '#f4c7c3'],
                                        ],
                                        'totebag' => [
                                            ['name' => 'Canvas', 'value' => '#f5f2ea'],
                                            ['name' => 'Natural', 'value' => '#e7ddc8'],
                                            ['name' => 'Charcoal', 'value' => '#4b5563'],
                                        ],
                                    ];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Colors</p>
                                    <p class="mt-2 text-xs text-slate-500">
                                        One color per line. Accepted formats: "Name|#hex", "Name:#hex", or "#hex Name".
                                    </p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($mockupProducts as $productKey => $productLabel)
                                            @php
                                                $colorValue = $productColors[$productKey] ?? '';
                                                if (is_array($colorValue)) {
                                                    $colorValue = collect($colorValue)
                                                        ->map(fn ($color) => trim(($color['name'] ?? '')) . '|' . ($color['value'] ?? ''))
                                                        ->filter()
                                                        ->implode("\n");
                                                }
                                                $colorPlaceholder = collect($defaultColors[$productKey] ?? [])
                                                    ->map(fn ($color) => "{$color['name']}|{$color['value']}")
                                                    ->implode("\n");
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">{{ $productLabel }}</p>
                                                <textarea
                                                    name="sections[{{ $index }}][data][product_colors][{{ $productKey }}]"
                                                    rows="4"
                                                    placeholder="{{ $colorPlaceholder }}"
                                                    class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $colorValue }}</textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Mockup Images</p>
                                    <p class="mt-2 text-xs text-slate-500">Upload front/back images per product. Uploaded files override the URL.</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($mockupProducts as $productKey => $productLabel)
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="text-sm font-semibold text-slate-700">{{ $productLabel }}</p>
                                                <div class="mt-3 grid gap-3">
                                                    @foreach ($mockupViews as $viewKey)
                                                        @php
                                                            $viewRaw = $mockups[$productKey][$viewKey] ?? '';
                                                            $viewData = is_array($viewRaw) ? $viewRaw : [];
                                                            $defaultMockup = is_array($viewRaw)
                                                                ? ($viewData['default'] ?? '')
                                                                : (is_string($viewRaw) ? $viewRaw : '');
                                                        @endphp
                                                        <div class="rounded-lg border border-slate-200 p-3">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ ucfirst($viewKey) }} view</p>
                                                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                                                <input type="text"
                                                                       name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][default]"
                                                                       value="{{ $defaultMockup }}"
                                                                       placeholder="{{ ucfirst($viewKey) }} image URL"
                                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                                <input type="file"
                                                                       name="sections[{{ $index }}][data][mockups][{{ $productKey }}][{{ $viewKey }}][default_file]"
                                                                       accept="image/*"
                                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                            </div>
                                                            @if (!empty($defaultMockup))
                                                                <div class="mt-2 text-xs text-slate-500">
                                                                    Current: <a href="{{ $defaultMockup }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <textarea name="sections[{{ $index }}][data][raw]" rows="3" placeholder="Section content" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ json_encode($data) }}</textarea>
                            @endif
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="admin-btn-primary">Save Page</button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="admin-card">
                <h2 class="text-lg font-semibold">Add Section</h2>
                <p class="mt-2 text-sm text-slate-500">Choose a preset section type to add to this page.</p>
                <form method="POST" action="{{ route('admin.builder.update', $page) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $page->title }}">
                    <select name="add_section_type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="hero">Hero</option>
                        <option value="tiles">Tiles</option>
                        <option value="category_carousel">Category carousel</option>
                        <option value="collection_showcase">Collection showcase</option>
                        <option value="best_sellers">Best sellers</option>
                        <option value="gift_finder">Gift finder</option>
                        <option value="how_it_works">How it works</option>
                        <option value="cta">Call to action</option>
                        <option value="design_studio">Design studio</option>
                        <option value="shop_header">Shop header</option>
                        <option value="embroidery_page">Embroidery page</option>
                        <option value="designer_page">Designer page</option>
                        <option value="mockup_page">Mockup page</option>
                    </select>
                    <button type="submit" class="admin-btn-primary">Add Section</button>
                </form>
            </div>

            <div class="admin-card">
                <h2 class="text-lg font-semibold">Media Library</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Upload images or MP4 videos in the Media Library and paste the file URL into section fields.
                </p>
                <a href="{{ route('admin.media.index') }}" class="mt-4 inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-600">Open Media Library</a>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('[data-template-add]').forEach((button) => {
            const container = button.closest('.rounded-2xl') ?? button.parentElement;
            const list = container ? container.querySelector('[data-template-list]') : null;
            if (!list) {
                return;
            }

            button.addEventListener('click', () => {
                const sectionIndex = list.dataset.templateSection;
                if (sectionIndex === undefined) {
                    return;
                }
                let nextIndex = Number.parseInt(list.dataset.templateNextIndex || '0', 10);
                if (Number.isNaN(nextIndex)) {
                    nextIndex = list.children.length;
                }
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-xl border border-slate-200 p-4';
                wrapper.innerHTML = `
                    <p class="text-sm font-semibold text-slate-700">Template ${nextIndex + 1}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <input type="text"
                               name="sections[${sectionIndex}][data][templates][all_results][${nextIndex}][label]"
                               placeholder="Template label"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="text"
                               name="sections[${sectionIndex}][data][templates][all_results][${nextIndex}][image]"
                               placeholder="Template image URL"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="file"
                               name="sections[${sectionIndex}][data][templates][all_results][${nextIndex}][image_file]"
                               accept="image/*"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                `;
                list.appendChild(wrapper);
                list.dataset.templateNextIndex = String(nextIndex + 1);
            });
        });
    </script>
@endsection
