@php
    $header = $globalHeader ?? [];
    $logoUrl = $header['logo_url'] ?? null;
    $menu = $header['menu'] ?? [];
    $menu = array_values(array_filter($menu, static function ($item) {
        $label = strtolower(trim((string) ($item['label'] ?? '')));
        return !in_array($label, ['gift', 'gifts'], true);
    }));
    $hasJerseyLink = collect($menu)->contains(function ($item) {
        return strtolower(trim((string) ($item['label'] ?? ''))) === 'jersey';
    });
    if (!$hasJerseyLink) {
        $embroideryIndex = collect($menu)->search(function ($item) {
            return strtolower(trim((string) ($item['label'] ?? ''))) === 'embroidery';
        });

        $jerseyMenuItem = ['label' => 'Jersey', 'url' => '/jersey'];
        if ($embroideryIndex === false) {
            $menu[] = $jerseyMenuItem;
        } else {
            array_splice($menu, $embroideryIndex + 1, 0, [$jerseyMenuItem]);
        }
    }
    $hasDesigner3dLink = collect($menu)->contains(function ($item) {
        return strtolower(trim((string) ($item['label'] ?? ''))) === '3d designer';
    });
    if (!$hasDesigner3dLink) {
        $jerseyIndex = collect($menu)->search(function ($item) {
            return strtolower(trim((string) ($item['label'] ?? ''))) === 'jersey';
        });

        $designerMenuItem = ['label' => '3D Designer', 'url' => '/3d-designer'];
        if ($jerseyIndex === false) {
            $menu[] = $designerMenuItem;
        } else {
            array_splice($menu, $jerseyIndex + 1, 0, [$designerMenuItem]);
        }
    }
    $hasBlogLink = collect($menu)->contains(function ($item) {
        return strtolower(trim((string) ($item['label'] ?? ''))) === 'blog';
    });
    if (!$hasBlogLink) {
        $menu[] = ['label' => 'Blog', 'url' => '/blogs'];
    }
    $menuStrip = $header['menu_strip'] ?? [];
    $menuStripCta = $header['menu_strip_cta'] ?? null;
    $ctaPosition = $header['menu_strip_cta_position'] ?? 8;
    $announcementItems = $header['announcement_items'] ?? [];
    $announcementSpeed = $header['announcement_speed'] ?? 120;
    $announcementSpeed = is_numeric($announcementSpeed) ? (float) $announcementSpeed : 120;
    if ($announcementSpeed <= 0) {
        $announcementSpeed = 120;
    }
    if (empty($announcementItems)) {
        $announcementItems = [
            ['text' => 'Wear Your Vibe'],
            ['text' => 'Express Yourself'],
            ['text' => 'Prints That Speak'],
            ['text' => '10% Off All Orders', 'highlight' => true],
        ];
    }
    $announcementItems = array_values(array_filter($announcementItems, function ($item) {
        return !empty($item['text']);
    }));
@endphp

@if (!empty($announcementItems))
    <div class="bg-brand text-white">
        <div class="announcement-bar">
            <div class="announcement-track" data-announcement-speed="{{ $announcementSpeed }}">
                @foreach ([0, 1] as $clone)
                    <div class="announcement-group" @if ($clone === 1) aria-hidden="true" @endif>
                        @foreach ($announcementItems as $index => $item)
                            <span class="{{ !empty($item['highlight']) ? 'text-brand-sun' : '' }}">
                                {{ $item['text'] ?? '' }}
                            </span>
                            @if ($index < count($announcementItems) - 1)
                                <span class="announcement-dot">•</span>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<header class="sticky top-0 z-40 border-b border-black/5 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-4">
        <div class="flex items-center gap-3">
            <button class="icon-button mobile-menu-toggle lg:hidden" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu" data-mobile-menu-toggle>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18" />
                </svg>
            </button>
            <a class="flex items-center gap-3" href="/" aria-label="Home">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Nai Prints" class="h-12 w-12 rounded-full object-cover">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand text-white text-lg font-bold">
                        NA
                    </div>
                @endif
            </a>
        </div>

        <nav class="hidden items-center gap-5 text-sm font-semibold text-slate-700 lg:flex">
            @foreach ($menu as $item)
                <a class="nav-link" href="{{ $item['url'] ?? '#' }}">{{ $item['label'] ?? 'Menu' }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <button class="icon-button" aria-label="Search">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.03 4.11l3.43 3.43a.75.75 0 1 1-1.06 1.06l-3.43-3.43A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
            </button>
            <button class="icon-button" aria-label="Account">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5ZM4.5 15.75a5.5 5.5 0 0 1 11 0 .75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                </svg>
            </button>
            <button class="icon-button relative" aria-label="Wishlist">
                <span class="badge" data-wishlist-count>0</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3.17 5.5A3.5 3.5 0 0 1 9 4.12 3.5 3.5 0 0 1 14.83 5.5c0 1.63-.86 3.2-2.54 4.67-1.46 1.27-3.04 2.2-3.35 2.37a.6.6 0 0 1-.58 0c-.31-.17-1.89-1.1-3.35-2.37C3.33 8.7 2.17 7.13 2.17 5.5Z" />
                </svg>
            </button>
            <button class="icon-button relative" aria-label="Cart" data-cart-button>
                <span class="badge" data-cart-count>0</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M1.5 3.5a.75.75 0 0 1 .75-.75H4a.75.75 0 0 1 .72.54l.5 1.71h10.82a.75.75 0 0 1 .72.96l-1.6 5.6a.75.75 0 0 1-.72.54H7.05l.3 1.05h7.9a.75.75 0 0 1 0 1.5H6.7a.75.75 0 0 1-.72-.54L4.14 5.25H2.25a.75.75 0 0 1-.75-.75Z" />
                </svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="mobile-menu lg:hidden" data-mobile-menu>
        <div class="mobile-menu-inner">
            @foreach ($menu as $item)
                <a class="mobile-menu-link" href="{{ $item['url'] ?? '#' }}">{{ $item['label'] ?? 'Menu' }}</a>
            @endforeach
        </div>
    </div>
</header>

@if (!empty($menuStrip))
    <div class="menu-strip">
        <div class="menu-track">
            @foreach ($menuStrip as $index => $item)
                @if ($menuStripCta && $index === (int) $ctaPosition)
                    <span class="menu-divider"></span>
                    <a href="{{ $menuStripCta['url'] ?? '#' }}" class="menu-pill">
                        {{ $menuStripCta['label'] ?? 'See All Products' }}
                    </a>
                @endif
                @php
                    $badgeText = trim($item['badge'] ?? '');
                    $showBadge = $badgeText !== '' && strtolower($badgeText) !== 'free';
                @endphp
                <a href="{{ $item['url'] ?? '#' }}" class="menu-item {{ $showBadge ? 'menu-badge-wrap' : '' }}">
                    <span class="menu-icon">{{ $item['icon'] ?? '' }}</span>
                    <span class="menu-label">{{ $item['label'] ?? '' }}</span>
                    @if ($showBadge)
                        <span class="menu-badge">{{ $badgeText }}</span>
                    @endif
                </a>
            @endforeach
            @if ($menuStripCta && count($menuStrip) <= (int) $ctaPosition)
                <span class="menu-divider"></span>
                <a href="{{ $menuStripCta['url'] ?? '#' }}" class="menu-pill">
                    {{ $menuStripCta['label'] ?? 'See All Products' }}
                </a>
            @endif
        </div>
    </div>
@endif
