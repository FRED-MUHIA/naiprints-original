@extends('admin.layout')

@section('title', 'Page Builder')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr]">
        <div class="admin-card">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Pages</p>
            <h2 class="mt-2 text-2xl font-semibold">Edit page content</h2>
            <div class="mt-6 grid gap-4">
                @foreach ($pages as $page)
                    @continue($page->slug === 'designs')
                    @continue($page->slug === 'designer')
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $page->title }}</p>
                            <p class="text-xs text-slate-500">/{{ $page->slug }}</p>
                        </div>
                        <a href="{{ route('admin.builder.edit', $page) }}" class="admin-btn-primary">
                            Edit
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card">
                <h2 class="text-xl font-semibold">Global Header</h2>
                <form method="POST" action="{{ route('admin.builder.globals') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="header[_menu_managed]" value="1">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Logo URL</label>
                        <input type="text" name="header[logo_url]" value="{{ $header->content['logo_url'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Favicon URL</label>
                        <input type="text" name="header[favicon_url]" value="{{ $header->content['favicon_url'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Announcement Speed (px/sec)</label>
                            <input type="number" min="20" step="10" name="header[announcement_speed]" value="{{ $header->content['announcement_speed'] ?? 120 }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div class="flex items-end text-xs text-slate-500">
                            Higher = faster, lower = slower.
                        </div>
                    </div>
                    @php
                        $announcementItems = $header->content['announcement_items'] ?? [];
                        if (empty($announcementItems)) {
                            $announcementItems = \App\Support\PageDefaults::header()['announcement_items'] ?? [];
                        }
                        if (empty($announcementItems)) {
                            $announcementItems = [['text' => '', 'highlight' => false]];
                        }
                    @endphp
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Announcement Bar</label>
                        <div class="mt-2 grid gap-3" data-announcement-builder>
                            <div class="grid gap-3" data-announcement-list>
                                @foreach ($announcementItems as $index => $item)
                                    <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center" data-announcement-row data-index="{{ $index }}">
                                        <input type="text" name="header[announcement_items][{{ $index }}][text]" value="{{ $item['text'] ?? '' }}" placeholder="Text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                            <input type="hidden" name="header[announcement_items][{{ $index }}][highlight]" value="0">
                                            <input type="checkbox" name="header[announcement_items][{{ $index }}][highlight]" value="1" {{ !empty($item['highlight']) ? 'checked' : '' }}>
                                            Highlight
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="admin-btn-outline w-full justify-center" data-announcement-add>
                                + Add text
                            </button>
                            <template data-announcement-template>
                                <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center" data-announcement-row data-index="__INDEX__">
                                    <input type="text" name="header[announcement_items][__INDEX__][text]" value="" placeholder="Text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                        <input type="hidden" name="header[announcement_items][__INDEX__][highlight]" value="0">
                                        <input type="checkbox" name="header[announcement_items][__INDEX__][highlight]" value="1">
                                        Highlight
                                    </label>
                                </div>
                            </template>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Use + Add text to add more announcement items.</p>
                    </div>
                    @php
                        $menuItems = $header->content['menu'] ?? [];
                    @endphp
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Menu Items</label>
                        <div class="mt-2 grid gap-3" data-menu-builder>
                            <div class="grid gap-3" data-menu-list>
                                @foreach ($menuItems as $index => $item)
                                    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-menu-row data-index="{{ $index }}">
                                        <input type="text" name="header[menu][{{ $index }}][label]" value="{{ $item['label'] ?? '' }}" placeholder="Label" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text" name="header[menu][{{ $index }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-menu-remove>Remove</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="admin-btn-outline w-full justify-center" data-menu-add>
                                + Add menu item
                            </button>
                            <template data-menu-template>
                                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-menu-row data-index="__INDEX__">
                                    <input type="text" name="header[menu][__INDEX__][label]" value="" placeholder="Label" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text" name="header[menu][__INDEX__][url]" value="" placeholder="URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <button type="button" class="admin-btn-outline text-xs justify-center" data-menu-remove>Remove</button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Menu Strip CTA</label>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <input type="text" name="header[menu_strip_cta][label]" value="{{ $header->content['menu_strip_cta']['label'] ?? '' }}" placeholder="Label" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                            <input type="text" name="header[menu_strip_cta][url]" value="{{ $header->content['menu_strip_cta']['url'] ?? '' }}" placeholder="URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Menu Strip Items</label>
                        <div class="mt-2 grid gap-3" data-strip-builder>
                            <div class="grid gap-3" data-strip-list>
                                @foreach (($header->content['menu_strip'] ?? []) as $index => $item)
                                    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] sm:items-center" data-strip-row data-index="{{ $index }}">
                                        <input type="text" name="header[menu_strip][{{ $index }}][icon]" value="{{ $item['icon'] ?? '' }}" placeholder="Emoji" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text" name="header[menu_strip][{{ $index }}][label]" value="{{ $item['label'] ?? '' }}" placeholder="Label" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text" name="header[menu_strip][{{ $index }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text" name="header[menu_strip][{{ $index }}][badge]" value="{{ $item['badge'] ?? '' }}" placeholder="Badge" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-strip-remove>Remove</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="admin-btn-outline w-full justify-center" data-strip-add>
                                + Add menu strip item
                            </button>
                            <template data-strip-template>
                                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] sm:items-center" data-strip-row data-index="__INDEX__">
                                    <input type="text" name="header[menu_strip][__INDEX__][icon]" value="" placeholder="Emoji" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text" name="header[menu_strip][__INDEX__][label]" value="" placeholder="Label" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text" name="header[menu_strip][__INDEX__][url]" value="" placeholder="URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text" name="header[menu_strip][__INDEX__][badge]" value="" placeholder="Badge" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <button type="button" class="admin-btn-outline text-xs justify-center" data-strip-remove>Remove</button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <button type="submit" class="admin-btn-primary">Save Header</button>
                </form>
            </div>

            <div class="admin-card">
                <h2 class="text-xl font-semibold">Global Footer</h2>
                <form method="POST" action="{{ route('admin.builder.globals') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Logo URL</label>
                        <input type="text" name="footer[logo_url]" value="{{ $footer->content['logo_url'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Brand</label>
                            <input type="text" name="footer[brand]" value="{{ $footer->content['brand'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Description</label>
                            <input type="text" name="footer[description]" value="{{ $footer->content['description'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input type="text" name="footer[hours][0]" value="{{ $footer->content['hours'][0] ?? '' }}" placeholder="Hours label" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        <input type="text" name="footer[hours][1]" value="{{ $footer->content['hours'][1] ?? '' }}" placeholder="Hours value" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    @php
                        $footerColumns = $footer->content['columns'] ?? [];
                        if (empty($footerColumns)) {
                            $footerColumns = \App\Support\PageDefaults::footer()['columns'] ?? [];
                        }
                        if (empty($footerColumns)) {
                            $footerColumns = [['title' => '', 'links' => [['label' => '', 'url' => '']]]];
                        }
                    @endphp
                    <div class="grid gap-4" data-footer-builder>
                        <div class="grid gap-4" data-footer-columns>
                            @foreach ($footerColumns as $colIndex => $column)
                                @php
                                    $links = $column['links'] ?? [];
                                    if (empty($links)) {
                                        $links = [['label' => '', 'url' => '']];
                                    }
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4" data-footer-column data-index="{{ $colIndex }}">
                                    <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-center">
                                        <input type="text"
                                               name="footer[columns][{{ $colIndex }}][title]"
                                               value="{{ $column['title'] ?? '' }}"
                                               placeholder="Column title"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-column-remove>Remove column</button>
                                    </div>
                                    <div class="mt-3 grid gap-2" data-footer-links>
                                        @foreach ($links as $linkIndex => $link)
                                            <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-footer-link data-index="{{ $linkIndex }}">
                                                <input type="text"
                                                       name="footer[columns][{{ $colIndex }}][links][{{ $linkIndex }}][label]"
                                                       value="{{ $link['label'] ?? '' }}"
                                                       placeholder="Label"
                                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                                <input type="text"
                                                       name="footer[columns][{{ $colIndex }}][links][{{ $linkIndex }}][url]"
                                                       value="{{ $link['url'] ?? '' }}"
                                                       placeholder="URL"
                                                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                                <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-link-remove>Remove</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="admin-btn-outline mt-3 w-full justify-center" data-footer-link-add>
                                        + Add link
                                    </button>
                                    <template data-footer-link-template>
                                        <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-footer-link data-index="__LINK_INDEX__">
                                            <input type="text"
                                                   name="footer[columns][__COL_INDEX__][links][__LINK_INDEX__][label]"
                                                   value=""
                                                   placeholder="Label"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                            <input type="text"
                                                   name="footer[columns][__COL_INDEX__][links][__LINK_INDEX__][url]"
                                                   value=""
                                                   placeholder="URL"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                            <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-link-remove>Remove</button>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="admin-btn-outline w-full justify-center" data-footer-column-add>
                            + Add column
                        </button>
                        <template data-footer-column-template>
                            <div class="rounded-2xl border border-slate-200 p-4" data-footer-column data-index="__COL_INDEX__">
                                <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <input type="text"
                                           name="footer[columns][__COL_INDEX__][title]"
                                           value=""
                                           placeholder="Column title"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-column-remove>Remove column</button>
                                </div>
                                <div class="mt-3 grid gap-2" data-footer-links>
                                    <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-footer-link data-index="0">
                                        <input type="text"
                                               name="footer[columns][__COL_INDEX__][links][0][label]"
                                               value=""
                                               placeholder="Label"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text"
                                               name="footer[columns][__COL_INDEX__][links][0][url]"
                                               value=""
                                               placeholder="URL"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-link-remove>Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="admin-btn-outline mt-3 w-full justify-center" data-footer-link-add>
                                    + Add link
                                </button>
                                <template data-footer-link-template>
                                    <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto] sm:items-center" data-footer-link data-index="__LINK_INDEX__">
                                        <input type="text"
                                               name="footer[columns][__COL_INDEX__][links][__LINK_INDEX__][label]"
                                               value=""
                                               placeholder="Label"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text"
                                               name="footer[columns][__COL_INDEX__][links][__LINK_INDEX__][url]"
                                               value=""
                                               placeholder="URL"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-footer-link-remove>Remove</button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Copyright</label>
                        <input type="text" name="footer[copyright]" value="{{ $footer->content['copyright'] ?? '' }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                    </div>
                    <button type="submit" class="admin-btn-primary">Save Footer</button>
                </form>
            </div>

            <div class="admin-card">
                <h2 class="text-xl font-semibold">Floating WhatsApp Chat Button</h2>
                <form method="POST" action="{{ route('admin.builder.globals') }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">WhatsApp Number</label>
                            <input type="text" name="footer[whatsapp_number]" value="{{ $footer->content['whatsapp_number'] ?? '' }}" placeholder="254700000000" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Pre-filled Message</label>
                            <input type="text" name="footer[whatsapp_message]" value="{{ $footer->content['whatsapp_message'] ?? '' }}" placeholder="Hello I need assistance" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Tooltip Text</label>
                            <input type="text" name="footer[whatsapp_tooltip]" value="{{ $footer->content['whatsapp_tooltip'] ?? '' }}" placeholder="Chat with us on WhatsApp" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Scroll Reveal (px)</label>
                            <input type="number" name="footer[whatsapp_threshold]" min="0" value="{{ $footer->content['whatsapp_threshold'] ?? 180 }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                        <input type="hidden" name="footer[whatsapp_scroll]" value="0">
                        <input type="checkbox" name="footer[whatsapp_scroll]" value="1" {{ !empty($footer->content['whatsapp_scroll']) ? 'checked' : '' }}>
                        Enable scroll trigger
                    </label>
                    <button type="submit" class="admin-btn-primary">Save WhatsApp Settings</button>
                </form>
            </div>

            <div class="admin-card">
                <h2 class="text-xl font-semibold">Purchase Popup</h2>
                <form method="POST" action="{{ route('admin.builder.globals') }}" class="mt-4 space-y-4">
                    @csrf
                    @php
                        $popupContent = $purchasePopups->content ?? [];
                        $popupEnabled = $popupContent['enabled'] ?? true;
                        $popupInterval = $popupContent['interval'] ?? 12;
                        $popupItems = $popupContent['items'] ?? [];
                        if (empty($popupItems)) {
                            $popupItems = \App\Support\PageDefaults::purchasePopups()['items'] ?? [];
                        }
                        if (empty($popupItems)) {
                            $popupItems = [['name' => '', 'product' => '', 'time' => '', 'image' => '', 'url' => '']];
                        }
                    @endphp
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            <input type="hidden" name="purchase_popups[enabled]" value="0">
                            <input type="checkbox" name="purchase_popups[enabled]" value="1" {{ $popupEnabled ? 'checked' : '' }}>
                            Enable popups
                        </label>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Rotation Interval (seconds)</label>
                            <input type="number"
                                   min="4"
                                   max="300"
                                   step="1"
                                   name="purchase_popups[interval]"
                                   value="{{ $popupInterval }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Popup Items</label>
                        <p class="mt-1 text-xs text-slate-500">Select a product to use its name and image automatically. Use Media Library URLs to override.</p>
                        <div class="mt-3 grid gap-3" data-purchase-popup-builder>
                            <div class="grid gap-3" data-purchase-popup-list>
                                @foreach ($popupItems as $index => $item)
                                    <div class="grid gap-3 sm:grid-cols-[1.2fr_1fr_1fr_1fr_1.2fr_1fr_auto] sm:items-center"
                                         data-purchase-popup-row
                                         data-index="{{ $index }}">
                                        <select name="purchase_popups[items][{{ $index }}][product_id]"
                                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none"
                                                data-purchase-popup-product>
                                            <option value="">Select product</option>
                                            @foreach ($popupProducts as $product)
                                                <option value="{{ $product->id }}" {{ (string) ($item['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text"
                                               name="purchase_popups[items][{{ $index }}][name]"
                                               value="{{ $item['name'] ?? '' }}"
                                               placeholder="Name"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text"
                                               name="purchase_popups[items][{{ $index }}][product]"
                                               value="{{ $item['product'] ?? '' }}"
                                               placeholder="Product"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none"
                                               data-purchase-popup-product-name>
                                        <input type="text"
                                               name="purchase_popups[items][{{ $index }}][time]"
                                               value="{{ $item['time'] ?? '' }}"
                                               placeholder="Time ago"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text"
                                               name="purchase_popups[items][{{ $index }}][image]"
                                               value="{{ $item['image'] ?? '' }}"
                                               placeholder="Image URL"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <input type="text"
                                               name="purchase_popups[items][{{ $index }}][url]"
                                               value="{{ $item['url'] ?? '' }}"
                                               placeholder="Link URL"
                                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                        <button type="button" class="admin-btn-outline text-xs justify-center" data-purchase-popup-remove>Remove</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="admin-btn-outline w-full justify-center" data-purchase-popup-add>
                                + Add popup
                            </button>
                            <template data-purchase-popup-template>
                                <div class="grid gap-3 sm:grid-cols-[1.2fr_1fr_1fr_1fr_1.2fr_1fr_auto] sm:items-center"
                                     data-purchase-popup-row
                                     data-index="__INDEX__">
                                    <select name="purchase_popups[items][__INDEX__][product_id]"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none"
                                            data-purchase-popup-product>
                                        <option value="">Select product</option>
                                        @foreach ($popupProducts as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text"
                                           name="purchase_popups[items][__INDEX__][name]"
                                           value=""
                                           placeholder="Name"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text"
                                           name="purchase_popups[items][__INDEX__][product]"
                                           value=""
                                           placeholder="Product"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none"
                                           data-purchase-popup-product-name>
                                    <input type="text"
                                           name="purchase_popups[items][__INDEX__][time]"
                                           value=""
                                           placeholder="Time ago"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text"
                                           name="purchase_popups[items][__INDEX__][image]"
                                           value=""
                                           placeholder="Image URL"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <input type="text"
                                           name="purchase_popups[items][__INDEX__][url]"
                                           value=""
                                           placeholder="Link URL"
                                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-brand focus:outline-none">
                                    <button type="button" class="admin-btn-outline text-xs justify-center" data-purchase-popup-remove>Remove</button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <button type="submit" class="admin-btn-primary">Save Popups</button>
                </form>
            </div>
        </div>
    </div>
@endsection
