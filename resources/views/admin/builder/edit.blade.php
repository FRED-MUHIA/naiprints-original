@extends('admin.layout')

@section('title', 'Edit Page')

@section('content')
    @php
        $sections = $page->content['sections'] ?? [];
        $categories = collect($categories ?? []);
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
                                        <div class="grid gap-3 sm:grid-cols-4">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][kicker]" value="{{ $tile['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][title]" value="{{ $tile['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][theme]" value="{{ $tile['theme'] ?? '' }}" placeholder="Theme class" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            <input type="text" name="sections[{{ $index }}][data][tiles][{{ $tileIndex }}][product_link]" value="{{ $tile['product_link'] ?? '' }}" placeholder="Product link" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
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
                            @elseif ($type === 'product_steps')
                                @php
                                    $steps = $data['steps'] ?? [];
                                    if (empty($steps)) {
                                        $steps = \App\Support\PageDefaults::section('product_steps')['data']['steps'] ?? [];
                                    }
                                    $stepThemes = [
                                        'products' => 'Pick product artwork',
                                        'design' => 'Create design artwork',
                                        'delivery' => 'Leave the rest artwork',
                                    ];
                                @endphp
                                <div class="grid gap-3">
                                    @foreach ($steps as $stepIndex => $step)
                                        <div class="rounded-xl border border-slate-200 p-4">
                                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Step {{ $stepIndex + 1 }}</p>
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text"
                                                       name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][title]"
                                                       value="{{ $step['title'] ?? '' }}"
                                                       placeholder="Step title"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <select name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][theme]"
                                                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    @foreach ($stepThemes as $themeValue => $themeLabel)
                                                        <option value="{{ $themeValue }}" @selected(($step['theme'] ?? '') === $themeValue)>{{ $themeLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <textarea name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][body]"
                                                      rows="2"
                                                      placeholder="Step description"
                                                      class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $step['body'] ?? '' }}</textarea>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                                <input type="text"
                                                       name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][image]"
                                                       value="{{ $step['image'] ?? '' }}"
                                                       placeholder="Image URL"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text"
                                                       name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][image_alt]"
                                                       value="{{ $step['image_alt'] ?? '' }}"
                                                       placeholder="Image alt text"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="file"
                                                       name="sections[{{ $index }}][data][steps][{{ $stepIndex }}][image_file]"
                                                       accept="image/*"
                                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                            @if (!empty($step['image']))
                                                <div class="mt-2 text-xs text-slate-500">
                                                    Current: <a href="{{ $step['image'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                                </div>
                                            @endif
                                        </div>
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
                                @php
                                    $featuredCategoryId = $data['product_category_id'] ?? '';
                                    $featuredCategoryId = is_numeric($featuredCategoryId) ? (int) $featuredCategoryId : '';
                                @endphp
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][kicker]" value="{{ $data['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][meta_left]" value="{{ $data['meta_left'] ?? '' }}" placeholder="Meta left" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][meta_right]" value="{{ $data['meta_right'] ?? '' }}" placeholder="Meta right" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product category</label>
                                    <select name="sections[{{ $index }}][data][product_category_id]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <option value="">Default (Design)</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) $featuredCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @php
                                    $railSettings = $data['clothing_rail'] ?? [];
                                    $selectedRailCategoryId = $railSettings['category_id']
                                        ?? collect($railSettings['category_ids'] ?? [])->first();
                                    $selectedRailCategoryId = is_numeric($selectedRailCategoryId) ? (string) ((int) $selectedRailCategoryId) : '';
                                    $railAppearance = $railSettings['appearance'] ?? 'showroom';
                                    $railBackgroundColor = $railSettings['background_color'] ?? '#07090d';
                                    $railBackgroundImage = $railSettings['background_image'] ?? '';
                                @endphp
                                <fieldset class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <legend class="px-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Homepage clothing rail</legend>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input type="hidden" name="sections[{{ $index }}][data][clothing_rail][enabled]" value="0">
                                        <input type="checkbox" name="sections[{{ $index }}][data][clothing_rail][enabled]" value="1" @checked(filter_var($railSettings['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN))>
                                        Show the interactive product rail after the site header
                                    </label>
                                    <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                        Collection title
                                        <input type="text" name="sections[{{ $index }}][data][clothing_rail][title]" value="{{ $railSettings['title'] ?? 'Find your next favourite.' }}" maxlength="255" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal">
                                    </label>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                            Products to show (4–16)
                                            <input type="number" name="sections[{{ $index }}][data][clothing_rail][product_limit]" value="{{ $railSettings['product_limit'] ?? 10 }}" min="4" max="16" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal">
                                        </label>
                                        <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                            Auto advance interval (ms)
                                            <input type="number" name="sections[{{ $index }}][data][clothing_rail][autoplay_interval]" value="{{ $railSettings['autoplay_interval'] ?? 6500 }}" min="4000" max="12000" step="500" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal">
                                        </label>
                                    </div>
                                    <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                        Section background
                                        <select name="sections[{{ $index }}][data][clothing_rail][appearance]" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal">
                                            <option value="showroom" @selected($railAppearance === 'showroom')>Black showroom</option>
                                            <option value="navy" @selected($railAppearance === 'navy')>Nai Prints navy</option>
                                            <option value="custom" @selected($railAppearance === 'custom')>Custom color</option>
                                        </select>
                                    </label>
                                    <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                        Custom background color (select “Custom color” above to use it)
                                        <input type="color" name="sections[{{ $index }}][data][clothing_rail][background_color]" value="{{ $railBackgroundColor }}" class="h-11 w-20 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                                    </label>
                                    <label class="grid gap-2 text-xs font-semibold text-slate-600">
                                        Section background image
                                        @if ($railBackgroundImage)
                                            <img src="{{ $railBackgroundImage }}" alt="Current collection background" class="h-24 w-full rounded-xl object-cover">
                                            <label class="flex items-center gap-2 font-normal">
                                                <input type="checkbox" name="sections[{{ $index }}][data][clothing_rail][remove_background_image]" value="1">
                                                Remove current background image
                                            </label>
                                        @endif
                                        <input type="file" name="sections[{{ $index }}][data][clothing_rail][background_image_file]" accept="image/*" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-normal">
                                        <span class="font-normal">Upload JPG, PNG, WebP, or GIF up to 4 MB. Uploading a new image replaces the current one.</span>
                                    </label>
                                    <label class="grid gap-1 text-xs font-semibold text-slate-600">
                                        Products to show from
                                        <select name="sections[{{ $index }}][data][clothing_rail][category_id]" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal">
                                            <option value="" @selected($selectedRailCategoryId === '')>Automatic apparel selection</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected($selectedRailCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="font-normal">Choose one category to limit the rail to its available products.</span>
                                    </label>
                                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input type="hidden" name="sections[{{ $index }}][data][clothing_rail][autoplay]" value="0">
                                            <input type="checkbox" name="sections[{{ $index }}][data][clothing_rail][autoplay]" value="1" @checked(filter_var($railSettings['autoplay'] ?? true, FILTER_VALIDATE_BOOLEAN))>
                                            Auto advance (pauses on interaction)
                                        </label>
                                    </div>
                                </fieldset>
                            @elseif ($type === 'sale_feature')
                                @php
                                    $saleCategoryId = $data['product_category_id'] ?? '';
                                    $saleCategoryId = is_numeric($saleCategoryId) ? (int) $saleCategoryId : '';
                                    $saleEndsAt = $data['ends_at'] ?? '';
                                    try {
                                        $saleEndsAt = $saleEndsAt ? \Carbon\Carbon::parse($saleEndsAt)->format('Y-m-d\TH:i') : '';
                                    } catch (\Throwable $exception) {
                                        $saleEndsAt = '';
                                    }
                                @endphp
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="sections[{{ $index }}][data][title]" value="{{ $data['title'] ?? '' }}" placeholder="Sale headline" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="number" name="sections[{{ $index }}][data][discount_percent]" value="{{ $data['discount_percent'] ?? 30 }}" min="0" max="95" step="1" placeholder="Discount percent" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <textarea name="sections[{{ $index }}][data][body]" rows="2" placeholder="Sale description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $data['body'] ?? '' }}</textarea>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <input type="datetime-local" name="sections[{{ $index }}][data][ends_at]" value="{{ $saleEndsAt }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][cta_label]" value="{{ $data['cta_label'] ?? '' }}" placeholder="CTA label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][cta_url]" value="{{ $data['cta_url'] ?? '' }}" placeholder="CTA URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Artwork Image</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                        <input type="text"
                                               name="sections[{{ $index }}][data][hero_image]"
                                               value="{{ $data['hero_image'] ?? '' }}"
                                               placeholder="Image URL"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text"
                                               name="sections[{{ $index }}][data][hero_image_alt]"
                                               value="{{ $data['hero_image_alt'] ?? '' }}"
                                               placeholder="Image alt text"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file"
                                               name="sections[{{ $index }}][data][hero_image_file]"
                                               accept="image/*"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    @if (!empty($data['hero_image']))
                                        <div class="mt-2 text-xs text-slate-500">
                                            Current: <a href="{{ $data['hero_image'] }}" class="text-brand underline underline-offset-4" target="_blank" rel="noopener">View image</a>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sale product category</label>
                                    <select name="sections[{{ $index }}][data][product_category_id]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <option value="">Default featured products</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) $saleCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500">The timer counts down to the selected date and time.</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Theme colors</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <label class="text-xs font-semibold text-slate-600">
                                            Section background
                                            <input type="color" name="sections[{{ $index }}][data][background_color]" value="{{ $data['background_color'] ?? '#ffffff' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Artwork background
                                            <input type="color" name="sections[{{ $index }}][data][hero_background_color]" value="{{ $data['hero_background_color'] ?? '#f3f4f6' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Sale text color
                                            <input type="color" name="sections[{{ $index }}][data][sale_text_color]" value="{{ $data['sale_text_color'] ?? '#e11d2e' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Sale alternate color
                                            <input type="color" name="sections[{{ $index }}][data][sale_text_alt_color]" value="{{ $data['sale_text_alt_color'] ?? '#000000' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Text outline
                                            <input type="color" name="sections[{{ $index }}][data][sale_outline_color]" value="{{ $data['sale_outline_color'] ?? '#000000' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Timer background
                                            <input type="color" name="sections[{{ $index }}][data][countdown_background_color]" value="{{ $data['countdown_background_color'] ?? '#e11d2e' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600">
                                            Timer text
                                            <input type="color" name="sections[{{ $index }}][data][countdown_text_color]" value="{{ $data['countdown_text_color'] ?? '#ffffff' }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 px-2 py-1">
                                        </label>
                                    </div>
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
                            @elseif ($type === 'maasai_page')
                                @php
                                    $maasaiDefaults = \App\Support\PageDefaults::maasai()['sections'][0]['data'] ?? [];
                                    $slides = $data['slides'] ?? ($maasaiDefaults['slides'] ?? []);
                                    if (empty($slides)) {
                                        $slides = [
                                            ['kicker' => '', 'title' => '', 'body' => '', 'image' => '', 'alt' => '', 'primary' => ['label' => '', 'url' => '']],
                                        ];
                                    }
                                    $maasaiCategories = $data['categories'] ?? ($maasaiDefaults['categories'] ?? []);
                                    if (empty($maasaiCategories)) {
                                        $maasaiCategories = [
                                            ['title' => '', 'body' => '', 'image' => '', 'url' => '#maasai-products'],
                                        ];
                                    }
                                    $howItWorks = $data['how_it_works'] ?? ($maasaiDefaults['how_it_works'] ?? []);
                                    $howItWorksSteps = $howItWorks['steps'] ?? [];
                                    if (empty($howItWorksSteps)) {
                                        $howItWorksSteps = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }
                                    $productCollection = $data['product_collection'] ?? ($maasaiDefaults['product_collection'] ?? []);
                                    $selectedMaasaiCategoryIds = collect($data['product_category_ids'] ?? [])
                                        ->filter(fn ($categoryId) => is_numeric($categoryId))
                                        ->map(fn ($categoryId) => (string) ((int) $categoryId))
                                        ->all();
                                    if (empty($selectedMaasaiCategoryIds) && is_numeric($data['product_category_id'] ?? null)) {
                                        $selectedMaasaiCategoryIds = [(string) ((int) $data['product_category_id'])];
                                    }
                                    $maasaiProductLimit = $data['product_limit'] ?? 12;
                                    $fallbackProducts = $data['products'] ?? ($maasaiDefaults['products'] ?? []);
                                    if (empty($fallbackProducts)) {
                                        $fallbackProducts = [
                                            ['name' => '', 'price' => '', 'image' => ''],
                                        ];
                                    }
                                    $testimonials = $data['testimonials'] ?? ($maasaiDefaults['testimonials'] ?? []);
                                    $testimonialItems = $testimonials['items'] ?? [];
                                    if (!is_array($testimonialItems)) {
                                        $testimonialItems = [];
                                    }
                                    while (count($testimonialItems) < 6) {
                                        $testimonialItems[] = ['name' => '', 'date' => '', 'rating' => 5, 'body' => '', 'avatar' => '', 'source' => 'Google'];
                                    }
                                    $contentSteps = $data['content_steps'] ?? ($maasaiDefaults['content_steps'] ?? []);
                                    $contentStepItems = $contentSteps['items'] ?? [];
                                    if (!is_array($contentStepItems) || empty($contentStepItems)) {
                                        $contentStepItems = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }
                                    while (count($contentStepItems) < 4) {
                                        $contentStepItems[] = ['title' => '', 'body' => ''];
                                    }
                                    $cta = $data['cta'] ?? ($maasaiDefaults['cta'] ?? []);
                                    $patternStripImage = $data['pattern_strip_image'] ?? ($maasaiDefaults['pattern_strip_image'] ?? '');
                                @endphp
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hero Slides</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($slides as $slideIndex => $slide)
                                            @php
                                                $defaultSlide = $maasaiDefaults['slides'][$slideIndex] ?? [];
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="mb-3 text-sm font-semibold text-slate-700">Slide {{ $slideIndex + 1 }}</p>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][kicker]" value="{{ $slide['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][title]" value="{{ $slide['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                <textarea name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][body]" rows="2" placeholder="Body" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $slide['body'] ?? '' }}</textarea>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][image]" value="{{ $slide['image'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][alt]" value="{{ $slide['alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][overlay_image]" value="{{ $slide['overlay_image'] ?? ($defaultSlide['overlay_image'] ?? '') }}" placeholder="Bouncing overlay image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][overlay_alt]" value="{{ $slide['overlay_alt'] ?? ($defaultSlide['overlay_alt'] ?? '') }}" placeholder="Bouncing overlay alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][primary][label]" value="{{ $slide['primary']['label'] ?? '' }}" placeholder="Button label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][slides][{{ $slideIndex }}][primary][url]" value="{{ $slide['primary']['url'] ?? '' }}" placeholder="Button URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Pattern Strip</p>
                                    <p class="mt-1 text-xs text-slate-500">Shown on the Maasai page immediately after the slider.</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][pattern_strip_image]" value="{{ $patternStripImage }}" placeholder="Pattern strip image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file" name="sections[{{ $index }}][data][pattern_strip_image_file]" accept="image/*" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    @if (!empty($patternStripImage))
                                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                            <img src="{{ $patternStripImage }}" alt="Current Maasai pattern strip" class="h-8 w-full object-cover">
                                        </div>
                                    @endif
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">How It Works</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][how_it_works][kicker]" value="{{ $howItWorks['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][how_it_works][title]" value="{{ $howItWorks['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($howItWorksSteps as $stepIndex => $step)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][how_it_works][steps][{{ $stepIndex }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][how_it_works][steps][{{ $stepIndex }}][body]" value="{{ $step['body'] ?? '' }}" placeholder="Step description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Category Cards</p>
                                    <div class="mt-3 grid gap-4">
                                        @foreach ($maasaiCategories as $categoryIndex => $maasaiCategory)
                                            <div class="grid gap-3 sm:grid-cols-4">
                                                <input type="text" name="sections[{{ $index }}][data][categories][{{ $categoryIndex }}][title]" value="{{ $maasaiCategory['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][categories][{{ $categoryIndex }}][body]" value="{{ $maasaiCategory['body'] ?? '' }}" placeholder="Description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][categories][{{ $categoryIndex }}][image]" value="{{ $maasaiCategory['image'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][categories][{{ $categoryIndex }}][url]" value="{{ $maasaiCategory['url'] ?? '' }}" placeholder="Link URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product Collection</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][product_collection][kicker]" value="{{ $productCollection['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][product_collection][title]" value="{{ $productCollection['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][product_collection][view_all_label]" value="{{ $productCollection['view_all_label'] ?? '' }}" placeholder="View all label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][product_collection][view_all_url]" value="{{ $productCollection['view_all_url'] ?? '' }}" placeholder="View all URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_180px]">
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product categories</label>
                                            <input type="hidden" name="sections[{{ $index }}][data][_product_categories_managed]" value="1">
                                            <select name="sections[{{ $index }}][data][product_category_ids][]" multiple class="h-40 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected(in_array((string) $category->id, $selectedMaasaiCategoryIds, true))>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-slate-500">Hold Ctrl or Cmd to choose multiple. Leave empty to use the default Maasai/Shuka keyword search.</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Product limit</label>
                                            <input type="number" name="sections[{{ $index }}][data][product_limit]" value="{{ $maasaiProductLimit }}" min="1" max="48" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Fallback Products</p>
                                    <p class="mt-1 text-xs text-slate-500">These show only when no live products match the selected source.</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($fallbackProducts as $productIndex => $fallbackProduct)
                                            <div class="grid gap-3 sm:grid-cols-[1fr_140px_1fr]">
                                                <input type="text" name="sections[{{ $index }}][data][products][{{ $productIndex }}][name]" value="{{ $fallbackProduct['name'] ?? '' }}" placeholder="Name" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="number" name="sections[{{ $index }}][data][products][{{ $productIndex }}][price]" value="{{ $fallbackProduct['price'] ?? '' }}" min="0" step="1" placeholder="Price" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][products][{{ $productIndex }}][image]" value="{{ $fallbackProduct['image'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Testimonials</p>
                                    <p class="mt-1 text-xs text-slate-500">Reviews shown on the Maasai page before the footer. Empty review rows are ignored.</p>
                                    <input type="hidden" name="sections[{{ $index }}][data][_testimonials_managed]" value="1">
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][testimonials][kicker]" value="{{ $testimonials['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][testimonials][title]" value="{{ $testimonials['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-4 grid gap-4"
                                         data-maasai-testimonial-list
                                         data-maasai-section="{{ $index }}"
                                         data-maasai-testimonial-next-index="{{ count($testimonialItems) }}">
                                        @foreach ($testimonialItems as $testimonialIndex => $testimonial)
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="mb-3 text-sm font-semibold text-slate-700">Review {{ $testimonialIndex + 1 }}</p>
                                                <div class="grid gap-3 sm:grid-cols-[1fr_140px_120px]">
                                                    <input type="text" name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][name]" value="{{ $testimonial['name'] ?? '' }}" placeholder="Customer name" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][date]" value="{{ $testimonial['date'] ?? '' }}" placeholder="Date" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="number" name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][rating]" value="{{ $testimonial['rating'] ?? 5 }}" min="1" max="5" step="1" placeholder="Rating" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                                <textarea name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][body]" rows="2" placeholder="Review text" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $testimonial['body'] ?? '' }}</textarea>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                    <input type="text" name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][avatar]" value="{{ $testimonial['avatar'] ?? '' }}" placeholder="Avatar image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text" name="sections[{{ $index }}][data][testimonials][items][{{ $testimonialIndex }}][source]" value="{{ $testimonial['source'] ?? 'Google' }}" placeholder="Source e.g. Google" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button"
                                            class="mt-4 admin-btn-outline text-xs uppercase tracking-[0.3em]"
                                            data-maasai-testimonial-add>
                                        Add review
                                    </button>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Bottom Content Steps</p>
                                    <p class="mt-1 text-xs text-slate-500">Shown near the bottom of the Maasai page before the footer.</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][content_steps][kicker]" value="{{ $contentSteps['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][content_steps][title]" value="{{ $contentSteps['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-4 grid gap-4">
                                        @foreach ($contentStepItems as $stepIndex => $step)
                                            <div class="rounded-xl border border-slate-200 p-4">
                                                <p class="mb-3 text-sm font-semibold text-slate-700">Step {{ $stepIndex + 1 }}</p>
                                                <input type="text" name="sections[{{ $index }}][data][content_steps][items][{{ $stepIndex }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="Step title" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <textarea name="sections[{{ $index }}][data][content_steps][items][{{ $stepIndex }}][body]" rows="2" placeholder="Step body" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $step['body'] ?? '' }}</textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">CTA</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][kicker]" value="{{ $cta['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][title]" value="{{ $cta['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <textarea name="sections[{{ $index }}][data][cta][body]" rows="2" placeholder="Body" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $cta['body'] ?? '' }}</textarea>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                        <input type="text" name="sections[{{ $index }}][data][cta][image]" value="{{ $cta['image'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][image_alt]" value="{{ $cta['image_alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file" name="sections[{{ $index }}][data][cta][image_file]" accept="image/*" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    @if (!empty($cta['image']))
                                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                            <img src="{{ $cta['image'] }}" alt="{{ $cta['image_alt'] ?? 'Current CTA image' }}" class="h-32 w-full object-cover">
                                        </div>
                                    @endif
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][label]" value="{{ $cta['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][url]" value="{{ $cta['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][label]" value="{{ $cta['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][url]" value="{{ $cta['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
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
                            @elseif ($type === 'footer_notice')
                                <input type="text" name="sections[{{ $index }}][data][headline]" value="{{ $data['headline'] ?? '' }}" placeholder="Headline (bold)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                <textarea name="sections[{{ $index }}][data][message]" rows="3" placeholder="Notice message" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $data['message'] ?? '' }}</textarea>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <input type="text" name="sections[{{ $index }}][data][background_color]" value="{{ $data['background_color'] ?? '#efefef' }}" placeholder="Background color (#efefef)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][bar_color]" value="{{ $data['bar_color'] ?? '#ff4b00' }}" placeholder="Bar color (#ff4b00)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <input type="text" name="sections[{{ $index }}][data][text_color]" value="{{ $data['text_color'] ?? '#0f172a' }}" placeholder="Text color (#0f172a)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>
                                <p class="text-xs text-slate-500">Use hex colors in #RRGGBB format.</p>
                            @elseif ($type === 'jersey_page')
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

                                    $audiences = $data['audiences'] ?? [];
                                    $audienceItems = $audiences['items'] ?? [];
                                    if (empty($audienceItems)) {
                                        $audienceItems = [
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                            ['title' => '', 'body' => ''],
                                        ];
                                    }

                                    $designs = $data['designs'] ?? [];
                                    $designItems = $designs['items'] ?? [];
                                    if (empty($designItems)) {
                                        $designItems = [
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                            ['image_url' => '', 'image_alt' => ''],
                                        ];
                                    }

                                    $video = $data['video'] ?? [];
                                    $cta = $data['cta'] ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Hero</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][kicker]" value="{{ $hero['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][hero][highlight]" value="{{ $hero['highlight'] ?? '' }}" placeholder="Highlight text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][hero][title]" value="{{ $hero['title'] ?? '' }}" placeholder="Title" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <textarea name="sections[{{ $index }}][data][hero][body]" rows="3" placeholder="Body" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $hero['body'] ?? '' }}</textarea>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][hero][image]" value="{{ $hero['image'] ?? '' }}" placeholder="Left image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file"
                                               name="sections[{{ $index }}][data][hero][image_file]"
                                               accept="image/*"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][hero][image_alt]" value="{{ $hero['image_alt'] ?? '' }}" placeholder="Left image alt text" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
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
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Who We Serve</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][audiences][kicker]" value="{{ $audiences['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][audiences][title]" value="{{ $audiences['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($audienceItems as $audienceIndex => $audienceItem)
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input type="text" name="sections[{{ $index }}][data][audiences][items][{{ $audienceIndex }}][title]" value="{{ $audienceItem['title'] ?? '' }}" placeholder="Audience title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][audiences][items][{{ $audienceIndex }}][body]" value="{{ $audienceItem['body'] ?? '' }}" placeholder="Audience description" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Jersey Gallery</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][designs][kicker]" value="{{ $designs['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][designs][title]" value="{{ $designs['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach ($designItems as $designIndex => $designItem)
                                            <div class="grid gap-2">
                                                <input type="text" name="sections[{{ $index }}][data][designs][items][{{ $designIndex }}][image_url]" value="{{ $designItem['image_url'] ?? '' }}" placeholder="Image URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                <input type="text" name="sections[{{ $index }}][data][designs][items][{{ $designIndex }}][image_alt]" value="{{ $designItem['image_alt'] ?? '' }}" placeholder="Image alt text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Branding Video</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][video][kicker]" value="{{ $video['kicker'] ?? '' }}" placeholder="Kicker" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][video][title]" value="{{ $video['title'] ?? '' }}" placeholder="Title" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <input type="text" name="sections[{{ $index }}][data][video][embed_url]" value="{{ $video['embed_url'] ?? '' }}" placeholder="YouTube embed URL" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                </div>

                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Call To Action</p>
                                    <input type="text" name="sections[{{ $index }}][data][cta][title]" value="{{ $cta['title'] ?? '' }}" placeholder="Title" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    <textarea name="sections[{{ $index }}][data][cta][body]" rows="3" placeholder="Body" class="mt-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $cta['body'] ?? '' }}</textarea>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][label]" value="{{ $cta['primary']['label'] ?? '' }}" placeholder="Primary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][primary][url]" value="{{ $cta['primary']['url'] ?? '' }}" placeholder="Primary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][label]" value="{{ $cta['secondary']['label'] ?? '' }}" placeholder="Secondary label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="sections[{{ $index }}][data][cta][secondary][url]" value="{{ $cta['secondary']['url'] ?? '' }}" placeholder="Secondary URL" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
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
                                                    One color per line. Use "Name|#hex", "Name:#hex", or "#hex Name" for exact custom colors. Common names like Red, Orange, Yellow, Green, Blue, Black, and White are also supported.
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
                                    $baseMockupProducts = [
                                        'tshirt' => 'Tshirt',
                                        'hoodie' => 'Hoodies',
                                        'polo' => 'Polo Tshirt',
                                        'cap' => 'Caps',
                                        'mug' => 'Mug',
                                        'pillow' => 'Pillow',
                                        'totebag' => 'Totebags',
                                    ];
                                    $productIcons = [
                                        'tshirt' => '👕',
                                        'hoodie' => '🧥',
                                        'polo' => '👔',
                                        'cap' => '🧢',
                                        'mug' => '☕',
                                        'pillow' => '🛏️',
                                        'totebag' => '👜',
                                    ];
                                    $productPrices = [
                                        'tshirt' => 1325,
                                        'hoodie' => 2500,
                                        'polo' => 1650,
                                        'cap' => 540,
                                        'mug' => 500,
                                        'pillow' => 1325,
                                        'totebag' => 600,
                                    ];
                                    $mockupViews = ['front', 'back'];
                                    $mockups = is_array($data['mockups'] ?? null) ? $data['mockups'] : [];
                                    $productItems = is_array($data['products'] ?? null) ? $data['products'] : [];
                                    $printAreas = is_array($data['print_areas'] ?? null) ? $data['print_areas'] : [];
                                    $mockupProducts = $baseMockupProducts;
                                    foreach ($productItems as $productKey => $productConfig) {
                                        if (array_key_exists($productKey, $mockupProducts) || !is_array($productConfig)) {
                                            continue;
                                        }
                                        $productName = trim((string) ($productConfig['name'] ?? ''));
                                        $mockupProducts[$productKey] = $productName !== ''
                                            ? $productName
                                            : ucwords(str_replace(['_', '-'], ' ', (string) $productKey));
                                    }
                                    $printAreaDefaults = [
                                        'tshirt' => ['x' => 260, 'y' => 260, 'w' => 300, 'h' => 360],
                                        'hoodie' => ['x' => 250, 'y' => 300, 'w' => 320, 'h' => 320],
                                        'polo' => ['x' => 270, 'y' => 260, 'w' => 280, 'h' => 320],
                                        'cap' => ['x' => 300, 'y' => 360, 'w' => 220, 'h' => 140],
                                        'mug' => ['x' => 260, 'y' => 340, 'w' => 300, 'h' => 200],
                                        'pillow' => ['x' => 230, 'y' => 280, 'w' => 360, 'h' => 360],
                                        'totebag' => ['x' => 260, 'y' => 320, 'w' => 300, 'h' => 320],
                                    ];
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
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Tiles</p>
                                    <p class="mt-2 text-xs text-slate-500">Edit the names and icons shown in the Product Mockups carousel. Icons can be emoji, text, or image URLs.</p>
                                    <div class="mt-3 grid gap-4"
                                         data-product-list
                                         data-product-section="{{ $index }}">
                                        @foreach ($mockupProducts as $productKey => $productLabel)
                                            @php
                                                $productConfig = is_array($productItems[$productKey] ?? null) ? $productItems[$productKey] : [];
                                                $productName = $productConfig['name'] ?? $productLabel;
                                                $productIcon = $productConfig['icon'] ?? ($productIcons[$productKey] ?? '');
                                                $productPrice = $productConfig['price'] ?? ($productPrices[$productKey] ?? 1325);
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4" data-product-card="{{ $productKey }}">
                                                <p class="text-sm font-semibold text-slate-700">{{ $productLabel }}</p>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][products][{{ $productKey }}][name]"
                                                           value="{{ $productName }}"
                                                           placeholder="Display name"
                                                           data-product-name="{{ $productKey }}"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="text"
                                                           name="sections[{{ $index }}][data][products][{{ $productKey }}][icon]"
                                                           value="{{ $productIcon }}"
                                                           placeholder="Icon emoji or image URL"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    <input type="number"
                                                           name="sections[{{ $index }}][data][products][{{ $productKey }}][price]"
                                                           value="{{ $productPrice }}"
                                                           min="0"
                                                           step="1"
                                                           placeholder="Checkout price"
                                                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button"
                                            class="admin-btn-outline mt-4 text-xs uppercase tracking-[0.3em]"
                                            data-product-add>
                                        Add product
                                    </button>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Print Areas</p>
                                    <p class="mt-2 text-xs text-slate-500">Adjust the artwork box for each product. X/Y control position; W/H control width and height.</p>
                                    <div class="mt-3 grid gap-4" data-product-print-area-list data-product-section="{{ $index }}">
                                        @foreach ($mockupProducts as $productKey => $productLabel)
                                            @php
                                                $areaConfig = is_array($printAreas[$productKey] ?? null) ? $printAreas[$productKey] : [];
                                                $areaDefaults = $printAreaDefaults[$productKey] ?? ['x' => 260, 'y' => 280, 'w' => 300, 'h' => 320];
                                                $area = array_merge($areaDefaults, array_intersect_key($areaConfig, $areaDefaults));
                                            @endphp
                                            <div class="rounded-xl border border-slate-200 p-4" data-product-print-area-card="{{ $productKey }}">
                                                <p class="text-sm font-semibold text-slate-700" data-product-heading="{{ $productKey }}">{{ $productLabel }}</p>
                                                <div class="mt-3 grid gap-3 sm:grid-cols-4">
                                                    @foreach (['x' => 'X', 'y' => 'Y', 'w' => 'Width', 'h' => 'Height'] as $areaKey => $areaLabel)
                                                        <input type="number"
                                                               name="sections[{{ $index }}][data][print_areas][{{ $productKey }}][{{ $areaKey }}]"
                                                               value="{{ $area[$areaKey] }}"
                                                               min="{{ in_array($areaKey, ['w', 'h'], true) ? 1 : 0 }}"
                                                               step="1"
                                                               placeholder="{{ $areaLabel }}"
                                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Product Colors</p>
                                    <p class="mt-2 text-xs text-slate-500">
                                        One color per line. Use "Name|#hex", "Name:#hex", or "#hex Name" for exact custom colors. Common names like Red, Orange, Yellow, Green, Blue, Black, and White are also supported.
                                    </p>
                                    <div class="mt-3 grid gap-4" data-product-colors-list data-product-section="{{ $index }}">
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
                                            <div class="rounded-xl border border-slate-200 p-4" data-product-colors-card="{{ $productKey }}">
                                                <p class="text-sm font-semibold text-slate-700" data-product-heading="{{ $productKey }}">{{ $productLabel }}</p>
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
                                    <div class="mt-3 grid gap-4" data-product-images-list data-product-section="{{ $index }}">
                                        @foreach ($mockupProducts as $productKey => $productLabel)
                                            <div class="rounded-xl border border-slate-200 p-4" data-product-images-card="{{ $productKey }}">
                                                <p class="text-sm font-semibold text-slate-700" data-product-heading="{{ $productKey }}">{{ $productLabel }}</p>
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
                        <option value="product_steps">Product steps</option>
                        <option value="category_carousel">Category carousel</option>
                        <option value="collection_showcase">Collection showcase</option>
                        <option value="best_sellers">Best sellers</option>
                        <option value="sale_feature">Sale feature</option>
                        <option value="gift_finder">Gift finder</option>
                        <option value="how_it_works">How it works</option>
                        <option value="cta">Call to action</option>
                        <option value="design_studio">Design studio</option>
                        <option value="footer_notice">Footer notice</option>
                        <option value="shop_header">Shop header</option>
                        <option value="maasai_page">Maasai page</option>
                        <option value="embroidery_page">Embroidery page</option>
                        <option value="jersey_page">Jersey page</option>
                        <option value="corporate_page">Corporate page</option>
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

        document.querySelectorAll('[data-maasai-testimonial-add]').forEach((button) => {
            const container = button.closest('.rounded-xl') ?? button.parentElement;
            const list = container ? container.querySelector('[data-maasai-testimonial-list]') : null;
            if (!list) {
                return;
            }

            button.addEventListener('click', () => {
                const sectionIndex = list.dataset.maasaiSection;
                if (sectionIndex === undefined) {
                    return;
                }
                let nextIndex = Number.parseInt(list.dataset.maasaiTestimonialNextIndex || '0', 10);
                if (Number.isNaN(nextIndex)) {
                    nextIndex = list.children.length;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-xl border border-slate-200 p-4';
                wrapper.innerHTML = `
                    <p class="mb-3 text-sm font-semibold text-slate-700">Review ${nextIndex + 1}</p>
                    <div class="grid gap-3 sm:grid-cols-[1fr_140px_120px]">
                        <input type="text"
                               name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][name]"
                               placeholder="Customer name"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="text"
                               name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][date]"
                               placeholder="Date"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="number"
                               name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][rating]"
                               value="5"
                               min="1"
                               max="5"
                               step="1"
                               placeholder="Rating"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <textarea name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][body]"
                              rows="2"
                              placeholder="Review text"
                              class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <input type="text"
                               name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][avatar]"
                               placeholder="Avatar image URL"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="text"
                               name="sections[${sectionIndex}][data][testimonials][items][${nextIndex}][source]"
                               value="Google"
                               placeholder="Source e.g. Google"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                `;
                list.appendChild(wrapper);
                list.dataset.maasaiTestimonialNextIndex = String(nextIndex + 1);
            });
        });

        const escapeHtml = (value) => String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const updateProductHeadings = (key, label) => {
            document.querySelectorAll(`[data-product-heading="${key}"]`).forEach((heading) => {
                heading.textContent = label || 'New product';
            });
        };

        document.querySelectorAll('[data-product-name]').forEach((input) => {
            input.addEventListener('input', () => {
                updateProductHeadings(input.dataset.productName, input.value.trim());
            });
        });

        document.querySelectorAll('[data-product-add]').forEach((button) => {
            const container = button.closest('.rounded-2xl') ?? button.parentElement;
            const list = container ? container.querySelector('[data-product-list]') : null;
            if (!list) {
                return;
            }

            button.addEventListener('click', () => {
                const sectionIndex = list.dataset.productSection;
                if (sectionIndex === undefined) {
                    return;
                }

                const key = `custom_${Date.now()}`;
                const form = button.closest('form') ?? document;
                const printAreaList = form.querySelector(`[data-product-print-area-list][data-product-section="${sectionIndex}"]`);
                const colorsList = form.querySelector(`[data-product-colors-list][data-product-section="${sectionIndex}"]`);
                const imagesList = form.querySelector(`[data-product-images-list][data-product-section="${sectionIndex}"]`);
                const productName = 'New product';

                const tile = document.createElement('div');
                tile.className = 'rounded-xl border border-slate-200 p-4';
                tile.dataset.productCard = key;
                tile.innerHTML = `
                    <p class="text-sm font-semibold text-slate-700">${productName}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <input type="text"
                               name="sections[${sectionIndex}][data][products][${key}][name]"
                               value="${productName}"
                               placeholder="Display name"
                               data-product-name="${key}"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="text"
                               name="sections[${sectionIndex}][data][products][${key}][icon]"
                               value="□"
                               placeholder="Icon emoji or image URL"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="number"
                               name="sections[${sectionIndex}][data][products][${key}][price]"
                               value="1325"
                               min="0"
                               step="1"
                               placeholder="Checkout price"
                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                `;
                list.appendChild(tile);

                const nameInput = tile.querySelector('[data-product-name]');
                if (nameInput) {
                    nameInput.addEventListener('input', () => {
                        const label = nameInput.value.trim();
                        tile.querySelector('p').textContent = label || productName;
                        updateProductHeadings(key, label);
                    });
                    nameInput.focus();
                    nameInput.select();
                }

                if (printAreaList) {
                    const printAreaCard = document.createElement('div');
                    printAreaCard.className = 'rounded-xl border border-slate-200 p-4';
                    printAreaCard.dataset.productPrintAreaCard = key;
                    printAreaCard.innerHTML = `
                        <p class="text-sm font-semibold text-slate-700" data-product-heading="${key}">${escapeHtml(productName)}</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-4">
                            ${[
                                ['x', 'X', 260, 0],
                                ['y', 'Y', 280, 0],
                                ['w', 'Width', 300, 1],
                                ['h', 'Height', 320, 1],
                            ].map(([areaKey, label, value, min]) => `
                                <input type="number"
                                       name="sections[${sectionIndex}][data][print_areas][${key}][${areaKey}]"
                                       value="${value}"
                                       min="${min}"
                                       step="1"
                                       placeholder="${label}"
                                       class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            `).join('')}
                        </div>
                    `;
                    printAreaList.appendChild(printAreaCard);
                }

                if (colorsList) {
                    const colorCard = document.createElement('div');
                    colorCard.className = 'rounded-xl border border-slate-200 p-4';
                    colorCard.dataset.productColorsCard = key;
                    colorCard.innerHTML = `
                        <p class="text-sm font-semibold text-slate-700" data-product-heading="${key}">${escapeHtml(productName)}</p>
                        <textarea
                            name="sections[${sectionIndex}][data][product_colors][${key}]"
                            rows="4"
                            placeholder="White|#f9fafb&#10;Black|#111827&#10;Natural|#e7ddc8"
                            class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                    `;
                    colorsList.appendChild(colorCard);
                }

                if (imagesList) {
                    const imageCard = document.createElement('div');
                    imageCard.className = 'rounded-xl border border-slate-200 p-4';
                    imageCard.dataset.productImagesCard = key;
                    imageCard.innerHTML = `
                        <p class="text-sm font-semibold text-slate-700" data-product-heading="${key}">${escapeHtml(productName)}</p>
                        <div class="mt-3 grid gap-3">
                            ${['front', 'back'].map((viewKey) => `
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">${viewKey.charAt(0).toUpperCase() + viewKey.slice(1)} view</p>
                                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                        <input type="text"
                                               name="sections[${sectionIndex}][data][mockups][${key}][${viewKey}][default]"
                                               placeholder="${viewKey.charAt(0).toUpperCase() + viewKey.slice(1)} image URL"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                        <input type="file"
                                               name="sections[${sectionIndex}][data][mockups][${key}][${viewKey}][default_file]"
                                               accept="image/*"
                                               class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    imagesList.appendChild(imageCard);
                }
            });
        });
    </script>
@endsection
