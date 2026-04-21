<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Nai Prints | Mockup Studio</title>
        <meta name="description" content="Create product mockups with simple, Canva-inspired controls. Upload artwork, place it, and export realistic previews.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="mockup-app">
        @php
            $pageSections = $pageContent['sections'] ?? [];
            $mockupSection = collect($pageSections)->firstWhere('type', 'mockup_page') ?? [];
            $mockupData = $mockupSection['data'] ?? [];
            $brandName = $mockupData['brand_name'] ?? 'Nai Prints';
            $brandMark = $mockupData['brand_mark'] ?? strtoupper(substr($brandName, 0, 2));
            $brandLogo = $mockupData['brand_logo'] ?? '';
            $topbarTitle = $mockupData['topbar_title'] ?? 'Untitled design - 1080px × 1080px';
            $topMenu = $mockupData['top_menu'] ?? ['File', 'Resize', 'Editing'];
            if (!is_array($topMenu) || empty($topMenu)) {
                $topMenu = ['File', 'Resize', 'Editing'];
            }
            $fileTitle = $mockupData['file_title'] ?? 'Untitled design';
            $fileSize = $mockupData['file_size'] ?? '1080px × 1080px';
            $saveLabel = $mockupData['save_label'] ?? 'Save design';
            $addToCartLabel = $mockupData['add_to_cart_label'] ?? 'Add to cart';
            $addToCartUrl = $mockupData['add_to_cart_url'] ?? '#';
            $backToShopLabel = $mockupData['back_to_shop_label'] ?? 'Back to shop';
            $backToShopUrl = $mockupData['back_to_shop_url'] ?? '/';
            $shareLabel = $mockupData['share_label'] ?? 'Share';
            $railLabels = $mockupData['rail_labels'] ?? [
                'mockups' => 'Products',
                'templates' => 'Templates',
                'apps' => 'Apps',
                'uploads' => 'Uploads',
                'edit' => 'Edit',
                'qr' => 'QR Code',
                'voice' => 'AI Voice',
            ];
            $templateResults = $mockupData['templates']['all_results'] ?? [];
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
                ->filter(fn ($item) => !empty($item['image']))
                ->values()
                ->all();
        @endphp
        <div class="mockup-shell">
            <header class="mockup-topbar">
                <div class="mockup-topbar-left">
                    <button class="mockup-icon-btn mockup-mobile-toggle" type="button" data-mobile-toggle aria-label="Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <a class="mockup-logo" href="/" aria-label="Home">
                        <img class="mockup-logo-image"
                             data-brand-logo
                             data-default-logo="{{ $brandLogo }}"
                             src="{{ $brandLogo }}"
                             alt="{{ $brandName }}"
                             @if (empty($brandLogo)) hidden @endif>
                        <span class="mockup-logo-mark"
                              data-brand-mark
                              data-default-mark="{{ $brandMark }}"
                              @if (!empty($brandLogo)) hidden @endif>{{ $brandMark }}</span>
                        <span class="mockup-logo-text">{{ $brandName }}</span>
                    </a>
                    <div class="mockup-topbar-group" data-topbar-group>
                        <div class="mockup-topbar-links">
                            @foreach ($topMenu as $menuItem)
                                @php
                                    $menuLabel = $menuItem;
                                    $menuKey = strtolower(trim($menuLabel));
                                @endphp
                                @if ($menuKey === 'file')
                                    <div class="mockup-file-menu">
                                        <button class="mockup-topbar-link" type="button" data-file-toggle>{{ $menuLabel }}</button>
                                        <div class="mockup-file-dropdown" data-file-menu hidden>
                                            <div class="mockup-file-header">
                                                <div>
                                                    <strong>{{ $fileTitle }}</strong>
                                                    <span>{{ $fileSize }}</span>
                                                </div>
                                                <button class="mockup-file-icon" type="button" aria-label="Edit name">✎</button>
                                            </div>
                                            <div class="mockup-file-section">
                                                <button class="mockup-file-item" type="button" data-file-action="new">
                                                    <span>Create new design</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="upload">
                                                    <span>Upload files</span>
                                                </button>
                                            </div>
                                            <div class="mockup-file-section">
                                                <button class="mockup-file-item" type="button" data-file-action="settings">
                                                    <span>Settings</span>
                                                    <span class="mockup-file-meta">›</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="accessibility">
                                                    <span>Accessibility</span>
                                                    <span class="mockup-file-meta">›</span>
                                                </button>
                                            </div>
                                            <div class="mockup-file-section">
                                                <button class="mockup-file-item" type="button" data-file-action="save">
                                                    <span>Save</span>
                                                    <span class="mockup-file-meta">All changes saved</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="move">
                                                    <span>Move</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="copy">
                                                    <span>Make a copy</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="download">
                                                    <span>Download</span>
                                                </button>
                                                <button class="mockup-file-item" type="button" data-file-action="history">
                                                    <span>Version history</span>
                                                </button>
                                                <button class="mockup-file-item danger" type="button" data-file-action="trash">
                                                    <span>Move to Trash</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <button class="mockup-topbar-link" type="button">{{ $menuLabel }}</button>
                                @endif
                            @endforeach
                        </div>
                        <div class="mockup-topbar-actions">
                            <button class="mockup-icon-btn" type="button" data-undo aria-label="Undo">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9 7L4 12L9 17" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 12H5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <button class="mockup-icon-btn" type="button" data-redo aria-label="Redo">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15 7L20 12L15 17" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4 12H19" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mockup-topbar-center">
                    {{ $topbarTitle }}
                </div>
                <div class="mockup-topbar-right">
                    <button class="mockup-trial" type="button" data-save-design>{{ $saveLabel }}</button>
                    <button class="mockup-icon-btn" type="button" aria-label="Chat">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" stroke="currentColor" stroke-width="2" fill="none" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="mockup-shop-actions">
                        <a class="mockup-shop-btn is-cart" href="{{ $addToCartUrl }}">{{ $addToCartLabel }}</a>
                        <a class="mockup-shop-btn is-back" href="{{ $backToShopUrl }}">{{ $backToShopLabel }}</a>
                    </div>
                    <div class="mockup-share" data-share>
                        <button class="mockup-share-btn" type="button" data-share-toggle>{{ $shareLabel }}</button>
                        <div class="mockup-share-menu">
                            <label class="mockup-toggle">
                                <input type="checkbox" checked data-export-bg>
                                <span>Include background</span>
                            </label>
                            <button class="mockup-share-action" type="button" data-export="png">Download PNG</button>
                            <button class="mockup-share-action" type="button" data-export="jpg">Download JPG</button>
                            <button class="mockup-share-action" type="button" data-export="zip">Download ZIP (Front + Back)</button>
                        </div>
                    </div>
                </div>
                <div class="mockup-mobile-drawer" data-mobile-drawer hidden></div>
            </header>

            <div class="mockup-body">
                <nav class="mockup-rail" aria-label="Primary tools">
                    <button class="mockup-rail-btn is-active" type="button" data-panel="mockups">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 6l4-2 4 2 3 2-2 4-2-1v8H9v-8l-2 1-2-4z" stroke="currentColor" stroke-width="1.7" fill="none" stroke-linejoin="round"/>
                                <path d="M10 8h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['mockups'] ?? 'Products' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="templates">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="13" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="4" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="13" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['templates'] ?? 'Templates' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="apps">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="14" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="4" y="14" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <path d="M14 17h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M17 14v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['apps'] ?? 'Apps' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="uploads">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 8a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.7" fill="none" stroke-linejoin="round"/>
                                <path d="M4 9h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['uploads'] ?? 'Uploads' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="edit">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 20h5l9-9-5-5-9 9v5z" stroke="currentColor" stroke-width="1.7" fill="none" stroke-linejoin="round"/>
                                <path d="M12 6l5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['edit'] ?? 'Edit' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="qr">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="14" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <rect x="4" y="14" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <path d="M14 14h3v3h-3zM17 17h3" stroke="currentColor" stroke-width="1.7" fill="none" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['qr'] ?? 'QR Code' }}</span>
                    </button>
                    <button class="mockup-rail-btn" type="button" data-panel="voice">
                        <span class="mockup-rail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 4a3 3 0 0 1 3 3v4a3 3 0 0 1-6 0V7a3 3 0 0 1 3-3z" stroke="currentColor" stroke-width="1.7" fill="none"/>
                                <path d="M6 11a6 6 0 0 0 12 0M12 17v3" stroke="currentColor" stroke-width="1.7" fill="none" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>{{ $railLabels['voice'] ?? 'AI Voice' }}</span>
                    </button>
                </nav>

                <aside class="mockup-panel is-mockups">
                    <div class="mockup-panel-tabs" data-panel-section="mockups uploads">
                        <button class="mockup-panel-tab is-active" type="button" data-mockup-tab="all">All</button>
                        <button class="mockup-panel-tab" type="button" data-mockup-tab="designs">Designs</button>
                        <button class="mockup-panel-tab" type="button" data-mockup-tab="folders">Folders</button>
                    </div>

                    <div class="mockup-panel-section mockup-edit-section" data-panel-section="edit">
                        <h3>Edit tools</h3>
                        <div class="mockup-color-palette">
                            <span class="mockup-color-label">
                                Color
                            </span>
                            <div class="mockup-color-dots" data-edit-color-row></div>
                        </div>
                        <div class="mockup-edit-grid">
                            <button class="mockup-edit-tile is-active" type="button" data-tool="select">
                                <span class="mockup-edit-icon">↖</span>
                                <span>Selection</span>
                            </button>
                            <button class="mockup-edit-tile" type="button" data-tool="text">
                                <span class="mockup-edit-icon">T</span>
                                <span>Text edit</span>
                            </button>
                            <button class="mockup-edit-tile" type="button" data-tool="table">
                                <span class="mockup-edit-icon">▦</span>
                                <span>Tables</span>
                            </button>
                            <button class="mockup-edit-tile" type="button" data-tool="sticky">
                                <span class="mockup-edit-icon">🗒</span>
                                <span>Sticky notes</span>
                            </button>
                            <button class="mockup-edit-tile" type="button" data-tool="line">
                                <span class="mockup-edit-icon">／</span>
                                <span>Lines</span>
                            </button>
                            <button class="mockup-edit-tile" type="button" data-tool="shape">
                                <span class="mockup-edit-icon">◼</span>
                                <span>Shapes</span>
                            </button>
                        </div>
                    </div>

                    <div class="mockup-panel-section mockup-products-panel" data-panel-section="mockups" data-mockup-tab-section="all">
                        <h3>Product Mockups</h3>
                        <div class="mockup-product-carousel" data-product-carousel>
                            <button class="mockup-carousel-btn is-left" type="button" data-product-prev aria-label="Scroll left">
                                <span aria-hidden="true">‹</span>
                            </button>
                            <div class="mockup-app-grid mockup-product-grid" data-product-track>
                                <button class="mockup-app-tile is-active" type="button" data-mockup="tshirt">
                                    <span class="mockup-app-icon">👕</span>
                                    <span>Tshirt</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="hoodie">
                                    <span class="mockup-app-icon">🧥</span>
                                    <span>Hoodies</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="polo">
                                    <span class="mockup-app-icon">👔</span>
                                    <span>Polo Tshirt</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="cap">
                                    <span class="mockup-app-icon">🧢</span>
                                    <span>Caps</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="mug">
                                    <span class="mockup-app-icon">☕</span>
                                    <span>Mug</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="pillow">
                                    <span class="mockup-app-icon">🛏️</span>
                                    <span>Pillow</span>
                                </button>
                                <button class="mockup-app-tile" type="button" data-mockup="totebag">
                                    <span class="mockup-app-icon">👜</span>
                                    <span>Totebags</span>
                                </button>
                            </div>
                            <button class="mockup-carousel-btn is-right" type="button" data-product-next aria-label="Scroll right">
                                <span aria-hidden="true">›</span>
                            </button>
                        </div>
                        <div class="mockup-color-section">
                            <h4 class="mockup-products-title mockup-colors-title">Colors</h4>
                            <div class="mockup-color-row" data-color-row></div>
                        </div>
                        <div class="mockup-view-section">
                            <h4 class="mockup-products-title">Views</h4>
                            <div class="mockup-view-row" data-view-row></div>
                        </div>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="templates">
                        <div class="template-ai-wrap">
                            <div class="template-ai-card">
                                <div class="template-ai-input">
                                    <input
                                        class="template-ai-field"
                                        type="text"
                                        placeholder="Describe your ideal design"
                                        data-template-input>
                                    <div class="template-ai-input-row">
                                        <button class="template-ai-icon" type="button" data-template-upload aria-label="Upload reference">
                                            <span>🖼️</span>
                                        </button>
                                    <div class="template-ai-actions">
                                        <button class="template-ai-submit" type="button" data-template-submit disabled aria-label="Generate">
                                            <span>➜</span>
                                        </button>
                                    </div>
                                    </div>
                                </div>
                                <input
                                    class="sr-only"
                                    type="file"
                                    accept="image/png,image/jpeg,image/svg+xml,.png,.jpg,.jpeg,.svg"
                                    multiple
                                    data-template-upload-input>

                                <div class="template-ai-tabs" data-template-tabs>
                                    <button class="template-ai-tab is-active" type="button" data-template-tab="templates">Templates</button>
                                    <button class="template-ai-tab" type="button" data-template-tab="styles">Styles</button>
                                </div>

                                <button class="template-ai-generate" type="button" data-template-generate>
                                    ✨ Generate design
                                </button>

                                <div class="template-ai-section">
                                    <h4>All results</h4>
                                    <div class="template-ai-grid" data-template-grid>
                                        @forelse ($templateResults as $template)
                                            @php
                                                $templateLabel = $template['label'] ?? 'Template design';
                                            @endphp
                                            <button class="template-ai-grid-item is-upload"
                                                    type="button"
                                                    title="{{ $templateLabel }}"
                                                    data-template-static
                                                    data-template-url="{{ $template['image'] }}">
                                                <img src="{{ $template['image'] }}" alt="{{ $templateLabel }}">
                                            </button>
                                        @empty
                                            <div class="template-ai-grid-item" data-template-placeholder></div>
                                            <div class="template-ai-grid-item" data-template-placeholder></div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="apps">
                        <h3>Mockup options</h3>
                        <div class="mockup-app-grid">
                            <button class="mockup-app-tile" type="button" data-panel-target="mockups">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5 16l4-4 4 4 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                        <path d="M6 6h12" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Mockups</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="4" y="6" width="16" height="12" rx="3" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <path d="M7 15l3-3 3 3 4-4" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>Photos</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8 5h8v4H8z" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <path d="M7 21h10M12 9v12" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>AI Voice</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <rect x="11" y="11" width="9" height="9" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                </span>
                                <span>Magic Media</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5 9h14M5 15h8" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                        <circle cx="17" cy="15" r="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                </span>
                                <span>Charts</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 7h10v10H7z" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <path d="M4 4h4v4H4zM16 16h4v4h-4z" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                </span>
                                <span>Bulk create</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M6 6h12v12H6z" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                </span>
                                <span>Data autofill</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M9 17V7l9 5-9 5z" stroke="currentColor" stroke-width="2" fill="none" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Videos</span>
                            </button>
                            <button class="mockup-app-tile" type="button">
                                <span class="mockup-app-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 4h10v6H7z" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <path d="M5 14h14v6H5z" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                </span>
                                <span>Captions</span>
                            </button>
                        </div>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="uploads mockups" data-mockup-tab-section="designs">
                        <div class="mockup-panel-title">
                            <h3>Designs</h3>
                            <div class="mockup-panel-actions">
                                <button class="mockup-panel-action" type="button" data-save-design>Save design</button>
                                <button class="mockup-panel-action" type="button">See all</button>
                            </div>
                        </div>
                        <p class="mockup-panel-empty" data-saved-empty>Saved designs will appear here.</p>
                        <div class="mockup-saved-grid" data-saved-list></div>
                        <div class="mockup-design-grid">
                            <button class="mockup-design-card mockup-upload-drop" type="button" data-upload-drop>
                                <div class="mockup-design-thumb">＋</div>
                                <div class="mockup-design-meta">
                                    <span>Upload artwork</span>
                                    <small>PNG / JPG / SVG</small>
                                </div>
                            </button>
                            <input class="sr-only" type="file" accept="image/*,.svg" multiple data-upload-input>
                            <div class="mockup-upload-grid" data-upload-list></div>
                        </div>
                    </div>

                    <div class="mockup-panel-section mockup-folders-panel" data-panel-section="mockups" data-mockup-tab-section="folders">
                        <h3>Folders</h3>
                        <p class="mockup-panel-empty">No folders yet.</p>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="edit mockups">
                        <h3>Alignment</h3>
                        <div class="mockup-btn-row">
                            <button class="mockup-pill" type="button" data-align="center-x">Center X</button>
                            <button class="mockup-pill" type="button" data-align="center-y">Center Y</button>
                        </div>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="edit mockups">
                        <h3>Layers & Style</h3>
                        <div class="mockup-btn-row">
                            <button class="mockup-pill" type="button" data-layer="forward">Bring Forward</button>
                            <button class="mockup-pill" type="button" data-layer="backward">Send Back</button>
                        </div>
                        <div class="mockup-btn-row">
                            <button class="mockup-pill" type="button" data-flip="x">Flip H</button>
                            <button class="mockup-pill" type="button" data-flip="y">Flip V</button>
                            <button class="mockup-pill danger" type="button" data-delete>Delete</button>
                        </div>
                        <label class="mockup-label" for="mockup-opacity">Opacity</label>
                        <input id="mockup-opacity" class="mockup-range" type="range" min="10" max="100" value="100" data-opacity>
                    </div>

                    <div class="mockup-panel-section" data-panel-section="qr voice">
                        <h3>Coming soon</h3>
                        <p class="mockup-panel-empty">More tools will appear here.</p>
                    </div>
                </aside>

                <main class="mockup-stage">
                    <div class="mockup-stage-toolbar">
                        <div class="mockup-stage-tabs">
                            <button class="mockup-stage-tab is-active" type="button" data-tools-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="mockup-tools-menu">Tools</button>
                            <button class="mockup-stage-tab" type="button" data-remove-bg>Remove Background</button>
                            <button class="mockup-stage-tab" type="button">Position</button>
                        </div>
                        <div class="mockup-stage-actions">
                            <button class="mockup-icon-btn" type="button" data-lock aria-label="Lock">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="2" fill="none"/>
                                </svg>
                            </button>
                            <button class="mockup-icon-btn" type="button" data-duplicate aria-label="Duplicate">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="7" y="7" width="10" height="10" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M4 9V5a2 2 0 0 1 2-2h8" stroke="currentColor" stroke-width="2" fill="none"/>
                                </svg>
                            </button>
                            <button class="mockup-icon-btn" type="button" data-delete aria-label="Delete">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 7h16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <rect x="6" y="7" width="12" height="13" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <button class="mockup-icon-btn" type="button" data-add aria-label="Add">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 6v12M6 12h12" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mockup-tools-menu" id="mockup-tools-menu" data-tools-menu hidden>
                            <div class="mockup-text-toolbar" data-text-toolbar>
                                <div class="mockup-toolbar-group">
                                    <select class="mockup-toolbar-select" data-text-font data-text-control>
                                        <option value="Montagna">Montagna</option>
                                        <option value="Inter">Inter</option>
                                        <option value="Poppins">Poppins</option>
                                        <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                        <option value="Albert Sans">Albert Sans</option>
                                    </select>
                                </div>
                                <div class="mockup-toolbar-group mockup-toolbar-size">
                                    <button class="mockup-toolbar-btn" type="button" data-text-size-dec data-text-control aria-label="Decrease size">−</button>
                                    <input class="mockup-toolbar-input" type="number" min="6" max="300" value="36" data-text-size data-text-control>
                                    <button class="mockup-toolbar-btn" type="button" data-text-size-inc data-text-control aria-label="Increase size">+</button>
                                </div>
                                <div class="mockup-toolbar-group">
                                    <label class="mockup-toolbar-color">
                                        <span>A</span>
                                        <input type="color" data-text-color data-text-control>
                                    </label>
                                    <button class="mockup-toolbar-btn" type="button" data-text-bold data-text-control aria-label="Bold">B</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-italic data-text-control aria-label="Italic">I</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-underline data-text-control aria-label="Underline">U</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-strike data-text-control aria-label="Strikethrough">S</button>
                                </div>
                                <div class="mockup-toolbar-group">
                                    <button class="mockup-toolbar-btn" type="button" data-text-case data-text-control aria-label="Letter case">aA</button>
                                </div>
                                <div class="mockup-toolbar-group">
                                    <button class="mockup-toolbar-btn" type="button" data-text-align="left" data-text-control aria-label="Align left">≡</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-align="center" data-text-control aria-label="Align center">≣</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-align="right" data-text-control aria-label="Align right">≡</button>
                                    <button class="mockup-toolbar-btn" type="button" data-text-align="justify" data-text-control aria-label="Justify">☰</button>
                                </div>
                                <div class="mockup-toolbar-group">
                                    <button class="mockup-toolbar-btn" type="button" data-text-spacing data-text-control aria-label="Spacing">↕︎</button>
                                    <div class="mockup-toolbar-popover" data-text-spacing-panel hidden>
                                        <label>
                                            Line height
                                            <input type="range" min="0.8" max="2.5" step="0.05" value="1.2" data-text-line-height data-text-control>
                                        </label>
                                        <label>
                                            Letter spacing
                                            <input type="range" min="0" max="200" step="5" value="0" data-text-letter-spacing data-text-control>
                                        </label>
                                    </div>
                                </div>
                                <button class="mockup-toolbar-link" type="button" data-text-effects data-text-control>Effects</button>
                                <button class="mockup-toolbar-link" type="button" data-text-animate data-text-control>Animate</button>
                                <button class="mockup-toolbar-link" type="button" data-text-position data-text-control>Position</button>
                                <button class="mockup-toolbar-btn" type="button" data-text-paint data-text-control aria-label="More tools">🎨</button>
                            </div>
                        </div>
                    </div>

                    <section class="mockup-canvas-panel">
                        <div class="mockup-canvas-wrap" data-canvas-wrap>
                            <div class="mockup-artboard-shell">
                                <div class="mockup-artboard">
                                <canvas id="mockup-canvas" width="600" height="600"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="mockup-flip-arrows" aria-label="Flip views">
                            <button class="mockup-flip-btn" type="button" data-view-nav="prev" aria-label="View previous side">↑</button>
                            <span class="mockup-flip-label">Flip</span>
                            <button class="mockup-flip-btn" type="button" data-view-nav="next" aria-label="View next side">↓</button>
                        </div>
                        <button class="mockup-add-page" type="button">+ Add page</button>
                    </section>

                    <footer class="mockup-bottombar">
                        <button class="mockup-notes" type="button">Notes</button>
                        <div class="mockup-zoom">
                            <input type="range" min="25" max="200" value="100" data-zoom-range>
                            <span class="mockup-zoom-label" data-zoom-label>100%</span>
                        </div>
                        <div class="mockup-page-meta">
                            <span>Pages</span>
                            <span>1 / 1</span>
                        </div>
                    </footer>
                </main>
            </div>
        </div>

        <div class="mockup-context-menu" data-context-menu>
            <button class="mockup-context-item" type="button" data-action="copy" data-requires-selection>
                <span>Copy</span>
                <kbd>Ctrl+C</kbd>
            </button>
            <button class="mockup-context-item" type="button" data-action="cut" data-requires-selection>
                <span>Cut</span>
                <kbd>Ctrl+X</kbd>
            </button>
            <button class="mockup-context-item" type="button" data-action="paste">
                <span>Paste</span>
                <kbd>Ctrl+V</kbd>
            </button>
            <button class="mockup-context-item" type="button" data-action="duplicate" data-requires-selection>
                <span>Duplicate</span>
                <kbd>Ctrl+D</kbd>
            </button>
            <button class="mockup-context-item danger" type="button" data-action="delete" data-requires-selection>
                <span>Delete</span>
                <kbd>DEL</kbd>
            </button>
            <div class="mockup-context-divider"></div>
            <div class="mockup-context-sub">
                <button class="mockup-context-item" type="button" data-action="align" data-requires-selection>
                    <span>Align to page</span>
                    <span>›</span>
                </button>
                <div class="mockup-context-submenu">
                    <button class="mockup-context-item" type="button" data-action="align-left" data-requires-selection>Left</button>
                    <button class="mockup-context-item" type="button" data-action="align-center" data-requires-selection>Center</button>
                    <button class="mockup-context-item" type="button" data-action="align-right" data-requires-selection>Right</button>
                    <button class="mockup-context-item" type="button" data-action="align-top" data-requires-selection>Top</button>
                    <button class="mockup-context-item" type="button" data-action="align-middle" data-requires-selection>Middle</button>
                    <button class="mockup-context-item" type="button" data-action="align-bottom" data-requires-selection>Bottom</button>
                </div>
            </div>
            <div class="mockup-context-sub">
                <button class="mockup-context-item" type="button" data-action="arrange" data-requires-selection>
                    <span>Arrange</span>
                    <span>›</span>
                </button>
                <div class="mockup-context-submenu">
                    <button class="mockup-context-item" type="button" data-action="send-back" data-requires-selection>Send to back</button>
                    <button class="mockup-context-item" type="button" data-action="send-backward" data-requires-selection>Send backward</button>
                    <button class="mockup-context-item" type="button" data-action="bring-forward" data-requires-selection>Bring forward</button>
                    <button class="mockup-context-item" type="button" data-action="bring-front" data-requires-selection>Bring to front</button>
                </div>
            </div>
            <button class="mockup-context-item" type="button" data-action="comment" data-requires-selection>
                <span>Comment</span>
                <kbd>Ctrl+Alt+N</kbd>
            </button>
            <button class="mockup-context-item" type="button" data-action="lock" data-requires-selection>
                <span data-lock-label>Lock</span>
                <kbd>Alt+Shift+L</kbd>
            </button>
            <button class="mockup-context-item" type="button" data-action="timing" data-requires-selection>
                <span>Show element timing</span>
            </button>
            <div class="mockup-context-divider"></div>
            <button class="mockup-context-item" type="button" data-action="apply-colors" data-requires-selection>
                <span>Apply colors to page</span>
            </button>
            <button class="mockup-context-item" type="button" data-action="download" data-requires-selection>
                <span>Download selection</span>
            </button>
        </div>

        <script>
            (() => {
                const canvasEl = document.getElementById('mockup-canvas');
                if (!canvasEl || !window.fabric) {
                    return;
                }

                const CANVAS_WIDTH = 600;
                const CANVAS_HEIGHT = 600;
                const SCALE_X = CANVAS_WIDTH / 820;
                const SCALE_Y = CANVAS_HEIGHT / 980;
                const MAX_HISTORY = 50;
                const GUIDE_SCALE = CANVAS_WIDTH / 760;
                const GUIDE_PRINT_DASH = [10 * GUIDE_SCALE, 8 * GUIDE_SCALE];
                const GUIDE_SAFE_DASH = [6 * GUIDE_SCALE, 8 * GUIDE_SCALE];
                const GUIDE_PRINT_STROKE = 2 * GUIDE_SCALE;
                const GUIDE_SAFE_STROKE = 1.5 * GUIDE_SCALE;
                const SAVED_STORAGE_KEY = 'nai_mockup_saved_designs';
                const MOCKUP_TAB_STORAGE_KEY = 'nai_mockup_active_tab';
                const SAVED_TTL_MS = 24 * 60 * 60 * 1000;
                const templateGenerateUrl = @json(route('mockup.ai.generate'));
                const csrfToken = @json(csrf_token());
                const BG_THEMES = {
                    light: '#f8fafc',
                    dark: '#0f172a',
                    studio: '#f3efe6',
                };
                const mockupPageData = @json($mockupData);
                const mockupPageMockups = (() => {
                    if (!mockupPageData || typeof mockupPageData !== 'object') {
                        return {};
                    }
                    const mockups = mockupPageData.mockups;
                    return mockups && typeof mockups === 'object' ? mockups : {};
                })();
                const mockupPageColors = (() => {
                    if (!mockupPageData || typeof mockupPageData !== 'object') {
                        return {};
                    }
                    const colors = mockupPageData.product_colors;
                    return colors && typeof colors === 'object' ? colors : {};
                })();

                const scalePrintArea = (area) => ({
                    x: area.x * SCALE_X,
                    y: area.y * SCALE_Y,
                    w: area.w * SCALE_X,
                    h: area.h * SCALE_Y,
                });

                const mockupConfig = {
                    tshirt: {
                        label: 'T-Shirt',
                        colors: [
                            { name: 'Classic White', value: '#f8fafc' },
                            { name: 'Ink Black', value: '#111827' },
                            { name: 'Sand', value: '#f5e9d8' },
                        ],
                        printArea: { x: 260, y: 260, w: 300, h: 360 },
                    },
                    hoodie: {
                        label: 'Hoodie',
                        colors: [
                            { name: 'Heather', value: '#e5e7eb' },
                            { name: 'Charcoal', value: '#1f2937' },
                            { name: 'Storm', value: '#334155' },
                        ],
                        printArea: { x: 250, y: 300, w: 320, h: 320 },
                    },
                    polo: {
                        label: 'Polo',
                        colors: [
                            { name: 'Ivory', value: '#f9fafb' },
                            { name: 'Navy', value: '#1e293b' },
                            { name: 'Olive', value: '#4b5d4a' },
                        ],
                        printArea: { x: 270, y: 260, w: 280, h: 320 },
                    },
                    cap: {
                        label: 'Caps',
                        colors: [
                            { name: 'Classic', value: '#f9fafb' },
                            { name: 'Midnight', value: '#0f172a' },
                            { name: 'Clay', value: '#d9b99b' },
                        ],
                        printArea: { x: 300, y: 360, w: 220, h: 140 },
                    },
                    mug: {
                        label: 'Mug',
                        colors: [
                            { name: 'Glossy', value: '#f9fafb' },
                            { name: 'Graphite', value: '#334155' },
                            { name: 'Cream', value: '#f3e9d9' },
                        ],
                        printArea: { x: 260, y: 340, w: 300, h: 200 },
                    },
                    pillow: {
                        label: 'Pillow',
                        colors: [
                            { name: 'Soft White', value: '#f9fafb' },
                            { name: 'Stone', value: '#cbd5f5' },
                            { name: 'Rose', value: '#f4c7c3' },
                        ],
                        printArea: { x: 230, y: 280, w: 360, h: 360 },
                    },
                    totebag: {
                        label: 'Totebags',
                        colors: [
                            { name: 'Canvas', value: '#f5f2ea' },
                            { name: 'Natural', value: '#e7ddc8' },
                            { name: 'Charcoal', value: '#4b5563' },
                        ],
                        printArea: { x: 260, y: 320, w: 300, h: 320 },
                    },
                };

                const viewLabels = {
                    front: 'Front',
                    back: 'Back',
                };

                const normalizeHex = (value) => {
                    if (typeof value !== 'string') {
                        return '';
                    }
                    const trimmed = value.trim();
                    if (!trimmed) {
                        return '';
                    }
                    if (trimmed.startsWith('#')) {
                        return trimmed;
                    }
                    if (/^[0-9a-fA-F]{3}$/.test(trimmed) || /^[0-9a-fA-F]{6}$/.test(trimmed)) {
                        return `#${trimmed}`;
                    }
                    return trimmed;
                };

                const parseColorLine = (line) => {
                    const raw = typeof line === 'string' ? line.trim() : '';
                    if (!raw) {
                        return null;
                    }
                    let name = '';
                    let value = '';
                    if (raw.includes('|')) {
                        const [left, right] = raw.split('|');
                        name = (left || '').trim();
                        value = (right || '').trim();
                    } else if (raw.includes(':')) {
                        const [left, right] = raw.split(':');
                        if ((left || '').includes('#')) {
                            value = (left || '').trim();
                            name = (right || '').trim();
                        } else {
                            name = (left || '').trim();
                            value = (right || '').trim();
                        }
                    } else {
                        const match = raw.match(/#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})/);
                        if (match) {
                            value = `#${match[1]}`;
                            name = raw.replace(match[0], '').trim();
                        }
                    }
                    if (!value) {
                        if (/^[a-zA-Z][a-zA-Z-]*$/.test(raw)) {
                            value = raw;
                            name = name || raw;
                        }
                    }
                    value = normalizeHex(value);
                    if (!value) {
                        return null;
                    }
                    if (!name) {
                        name = value.toUpperCase();
                    }
                    return { name, value };
                };

                const parseColorLines = (value) => {
                    if (Array.isArray(value)) {
                        return value
                            .map((entry) => {
                                if (typeof entry === 'string') {
                                    return parseColorLine(entry);
                                }
                                if (!entry || typeof entry !== 'object') {
                                    return null;
                                }
                                const name = entry.name || entry.label || entry.title || '';
                                const hex = normalizeHex(entry.value || entry.color || '');
                                if (!hex) {
                                    return null;
                                }
                                return { name: name || hex.toUpperCase(), value: hex };
                            })
                            .filter(Boolean);
                    }
                    if (typeof value !== 'string') {
                        return [];
                    }
                    const chunks = value
                        .split('\n')
                        .flatMap((line) => line.split(/[;,]/))
                        .map((line) => line.trim())
                        .filter(Boolean);
                    const parsed = [];
                    chunks.forEach((line) => {
                        const matches = [...line.matchAll(/#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})/g)];
                        if (matches.length > 1) {
                            matches.forEach((match) => {
                                const hex = normalizeHex(match[0]);
                                if (hex) {
                                    parsed.push({ name: hex.toUpperCase(), value: hex });
                                }
                            });
                            return;
                        }
                        const entry = parseColorLine(line);
                        if (entry) {
                            parsed.push(entry);
                        }
                    });
                    return parsed;
                };

                const runtimeProductColors = {};

                const getBaseProductColors = (mockupKey) => {
                    const raw = mockupPageColors?.[mockupKey];
                    const parsed = parseColorLines(raw);
                    if (parsed.length) {
                        return parsed;
                    }
                    return mockupConfig[mockupKey]?.colors || [];
                };

                const getProductColors = (mockupKey) => {
                    if (!runtimeProductColors[mockupKey]) {
                        runtimeProductColors[mockupKey] = getBaseProductColors(mockupKey).map((color) => ({ ...color }));
                    }
                    return runtimeProductColors[mockupKey];
                };

                const getMockupViews = (mockupKey) => {
                    const entry = mockupPageMockups?.[mockupKey];
                    if (entry && typeof entry === 'object' && !Array.isArray(entry)) {
                        const viewKeys = Object.keys(entry).filter((key) => ['front', 'back'].includes(key));
                        if (viewKeys.length) {
                            return viewKeys;
                        }
                    }
                    return ['front'];
                };

                const resolveView = (mockupKey, view) => {
                    const views = getMockupViews(mockupKey);
                    if (view && views.includes(view)) {
                        return view;
                    }
                    return views[0] || 'front';
                };

                const getMockupImageUrl = (mockupKey, view, color) => {
                    const entry = mockupPageMockups?.[mockupKey];
                    if (!entry) {
                        return '';
                    }
                    const colorKey = typeof color === 'string' ? color.replace('#', '').toLowerCase() : '';
                    let viewEntry = entry;
                    if (entry && typeof entry === 'object' && !Array.isArray(entry)) {
                        if (Object.prototype.hasOwnProperty.call(entry, view)) {
                            viewEntry = entry[view];
                        } else if (
                            Object.prototype.hasOwnProperty.call(entry, 'default') ||
                            Object.prototype.hasOwnProperty.call(entry, 'colors')
                        ) {
                            viewEntry = entry;
                        } else {
                            return '';
                        }
                    }
                    if (typeof viewEntry === 'string') {
                        return viewEntry;
                    }
                    if (viewEntry && typeof viewEntry === 'object') {
                        const colors = viewEntry.colors;
                        if (colors && typeof colors === 'object' && colorKey && colors[colorKey]) {
                            return colors[colorKey];
                        }
                        if (typeof viewEntry.default === 'string') {
                            return viewEntry.default;
                        }
                    }
                    return '';
                };

                const canvas = new fabric.Canvas(canvasEl, {
                    width: CANVAS_WIDTH,
                    height: CANVAS_HEIGHT,
                    preserveObjectStacking: true,
                    selectionColor: 'rgba(59, 130, 246, 0.12)',
                    selectionBorderColor: '#3b82f6',
                    selectionLineWidth: 2,
                });

                fabric.Object.prototype.cornerColor = '#3b82f6';
                fabric.Object.prototype.cornerStyle = 'circle';
                fabric.Object.prototype.transparentCorners = false;
                fabric.Object.prototype.padding = 6;

                let currentMockup = 'tshirt';
                let currentColorIndex = 0;
                let currentView = 'front';
                let currentBgTheme = 'light';
                let currentPrintArea = scalePrintArea(mockupConfig[currentMockup].printArea);
                let mockupImage = null;
                let printAreaRect = null;
                let safeAreaRect = null;
                let printAreaShadow = null;
                let history = [];
                let redoStack = [];
                let isRestoring = false;
                let clipboard = null;
                const viewDesigns = {};

                const uploadInput = document.querySelector('[data-upload-input]');
                const uploadDrops = document.querySelectorAll('[data-upload-drop]');
                const uploadList = document.querySelector('[data-upload-list]');
                const uploadBtn = document.querySelector('[data-upload-btn]');
                const saveDesignBtns = document.querySelectorAll('[data-save-design]');
                const savedList = document.querySelector('[data-saved-list]');
                const savedEmpty = document.querySelector('[data-saved-empty]');
                const toolButtons = document.querySelectorAll('[data-tool]');
                const railButtons = document.querySelectorAll('[data-panel]');
                const panelTargets = document.querySelectorAll('[data-panel-target]');
                const panelShell = document.querySelector('.mockup-panel');
                const panelSections = document.querySelectorAll('[data-panel-section]');
                const colorRow = document.querySelector('[data-color-row]');
                const editColorRow = document.querySelector('[data-edit-color-row]');
                const colorRing = document.querySelector('.mockup-color-ring');
                const viewRow = document.querySelector('[data-view-row]');
                const viewNavButtons = document.querySelectorAll('[data-view-nav]');
                const mobileToggle = document.querySelector('[data-mobile-toggle]');
                const mobileDrawer = document.querySelector('[data-mobile-drawer]');
                const topbarGroup = document.querySelector('[data-topbar-group]');
                const topbarRight = document.querySelector('.mockup-topbar-right');
                const shareWrap = document.querySelector('[data-share]');
                const shareToggle = document.querySelector('[data-share-toggle]');
                const toolsToggle = document.querySelector('[data-tools-toggle]');
                const toolsMenu = document.querySelector('[data-tools-menu]');
                const stageToolbar = document.querySelector('.mockup-stage-toolbar');
                const canvasWrap = document.querySelector('[data-canvas-wrap]');
                const artboard = document.querySelector('.mockup-artboard');
                const zoomRange = document.querySelector('[data-zoom-range]');
                const zoomLabel = document.querySelector('[data-zoom-label]');
                const undoBtn = document.querySelector('[data-undo]');
                const redoBtn = document.querySelector('[data-redo]');
                const exportBgToggle = document.querySelector('[data-export-bg]');
                const opacityRange = document.querySelector('[data-opacity]');
                const contextMenu = document.querySelector('[data-context-menu]');
                const contextLockLabel = document.querySelector('[data-lock-label]');
                const contextItems = document.querySelectorAll('[data-context-menu] [data-action]');
                const fileToggle = document.querySelector('[data-file-toggle]');
                const fileMenu = document.querySelector('[data-file-menu]');
                const mockupTabs = document.querySelectorAll('[data-mockup-tab]');
                const mockupTabSections = document.querySelectorAll('[data-mockup-tab-section]');
                const builderList = document.querySelector('[data-builder-list]');
                const builderEmpty = document.querySelector('[data-builder-empty]');
                const builderEditor = document.querySelector('[data-builder-editor]');
                const builderNameInput = document.querySelector('[data-builder-name]');
                const builderTextRow = document.querySelector('[data-builder-text-row]');
                const builderTextInput = document.querySelector('[data-builder-text]');
                const builderColorInput = document.querySelector('[data-builder-color]');
                const builderOpacityInput = document.querySelector('[data-builder-opacity]');
                const builderOpacityValue = document.querySelector('[data-builder-opacity-value]');
                const builderLockBtn = document.querySelector('[data-builder-lock]');
                const builderDeleteBtn = document.querySelector('[data-builder-delete]');
                const builderForwardBtn = document.querySelector('[data-builder-forward]');
                const builderBackwardBtn = document.querySelector('[data-builder-backward]');
                const builderRefreshBtn = document.querySelector('[data-builder-refresh]');
                const textToolbar = document.querySelector('[data-text-toolbar]');
                const textControls = document.querySelectorAll('[data-text-control]');
                const textColorInput = document.querySelector('[data-text-color]');
                const textSizeInput = document.querySelector('[data-text-size]');
                const textSizeDecBtn = document.querySelector('[data-text-size-dec]');
                const textSizeIncBtn = document.querySelector('[data-text-size-inc]');
                const textFontSelect = document.querySelector('[data-text-font]');
                const textBoldBtn = document.querySelector('[data-text-bold]');
                const textItalicBtn = document.querySelector('[data-text-italic]');
                const textUnderlineBtn = document.querySelector('[data-text-underline]');
                const textStrikeBtn = document.querySelector('[data-text-strike]');
                const textCaseBtn = document.querySelector('[data-text-case]');
                const textAlignBtns = document.querySelectorAll('[data-text-align]');
                const textSpacingBtn = document.querySelector('[data-text-spacing]');
                const textSpacingPanel = document.querySelector('[data-text-spacing-panel]');
                const textLineHeightInput = document.querySelector('[data-text-line-height]');
                const textLetterSpacingInput = document.querySelector('[data-text-letter-spacing]');
                const textEffectsBtn = document.querySelector('[data-text-effects]');
                const textAnimateBtn = document.querySelector('[data-text-animate]');
                const textPositionBtn = document.querySelector('[data-text-position]');
                const textPaintBtn = document.querySelector('[data-text-paint]');
                const removeBgBtn = document.querySelector('[data-remove-bg]');
                const brandUploadInput = document.querySelector('[data-brand-upload]');
                const brandClearBtn = document.querySelector('[data-brand-clear]');
                const brandLogoImg = document.querySelector('[data-brand-logo]');
                const brandMarkEl = document.querySelector('[data-brand-mark]');

                const isToolsMenuOpen = () => Boolean(toolsMenu?.classList.contains('is-open'));

                const setToolsMenuOpen = (open) => {
                    if (!toolsMenu) {
                        return;
                    }
                    toolsMenu.classList.toggle('is-open', open);
                    toolsMenu.hidden = !open;
                    toolsToggle?.setAttribute('aria-expanded', String(open));
                    stageToolbar?.classList.toggle('is-tools-open', open);
                    if (!open && textSpacingPanel) {
                        textSpacingPanel.hidden = true;
                    }
                };

                const toggleToolsMenu = () => {
                    setToolsMenuOpen(!isToolsMenuOpen());
                };

                const uploads = [];
                let activeTool = 'select';
                let activeColor = '#7c3aed';
                let activePanel = 'mockups';
                const MOCKUP_TAB_KEYS = ['all', 'designs', 'folders'];
                const readSavedMockupTab = () => {
                    try {
                        const stored = (localStorage.getItem(MOCKUP_TAB_STORAGE_KEY) || '').toLowerCase().trim();
                        return MOCKUP_TAB_KEYS.includes(stored) ? stored : 'all';
                    } catch (err) {
                        return 'all';
                    }
                };
                const saveMockupTab = (tab) => {
                    try {
                        localStorage.setItem(MOCKUP_TAB_STORAGE_KEY, tab);
                    } catch (err) {
                        // Ignore storage errors.
                    }
                };
                let activeMockupTab = readSavedMockupTab();
                const BRAND_LOGO_KEY = 'nai_mockup_brand_logo';

                const toDataUrl = (svg) => `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;

                const getSavedDesigns = () => {
                    try {
                        const raw = localStorage.getItem(SAVED_STORAGE_KEY);
                        const parsed = raw ? JSON.parse(raw) : [];
                        return Array.isArray(parsed) ? parsed : [];
                    } catch (err) {
                        return [];
                    }
                };

                const applyBrandLogo = (url) => {
                    const clean = typeof url === 'string' ? url.trim() : '';
                    if (brandLogoImg) {
                        if (clean) {
                            brandLogoImg.src = clean;
                            brandLogoImg.hidden = false;
                        } else {
                            const fallback = brandLogoImg.dataset.defaultLogo || '';
                            brandLogoImg.src = fallback;
                            brandLogoImg.hidden = !fallback;
                        }
                    }
                    if (brandMarkEl) {
                        const showLogo = Boolean(clean || (brandLogoImg && !brandLogoImg.hidden));
                        brandMarkEl.hidden = showLogo;
                    }
                };

                const loadBrandLogo = () => {
                    const stored = localStorage.getItem(BRAND_LOGO_KEY);
                    if (stored) {
                        applyBrandLogo(stored);
                        return;
                    }
                    applyBrandLogo(brandLogoImg?.dataset.defaultLogo || '');
                };

                const saveDesignsToStorage = (items) => {
                    localStorage.setItem(SAVED_STORAGE_KEY, JSON.stringify(items));
                };

                const pruneSavedDesigns = (items) => {
                    const now = Date.now();
                    return items.filter((item) => now - item.savedAt < SAVED_TTL_MS);
                };

                const formatSavedTime = (timestamp) => {
                    const date = new Date(timestamp);
                    return date.toLocaleString([], {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                };

                const createLayerId = () =>
                    `layer-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;

                const getDefaultLayerName = (obj) => {
                    if (!obj) {
                        return 'Layer';
                    }
                    if (obj.type === 'textbox' || obj.type === 'text') {
                        return 'Text';
                    }
                    if (obj.type === 'image') {
                        return 'Image';
                    }
                    if (obj.type === 'rect') {
                        return 'Shape';
                    }
                    if (obj.type === 'circle') {
                        return 'Circle';
                    }
                    if (obj.type === 'triangle') {
                        return 'Triangle';
                    }
                    if (obj.type === 'group') {
                        return 'Group';
                    }
                    return 'Layer';
                };

                const getLayerTypeLabel = (obj) => {
                    if (!obj?.type) {
                        return 'Layer';
                    }
                    if (obj.type === 'textbox' || obj.type === 'text') {
                        return 'Text';
                    }
                    if (obj.type === 'rect') {
                        return 'Shape';
                    }
                    if (obj.type === 'circle') {
                        return 'Circle';
                    }
                    if (obj.type === 'triangle') {
                        return 'Triangle';
                    }
                    if (obj.type === 'image') {
                        return 'Image';
                    }
                    if (obj.type === 'group') {
                        return 'Group';
                    }
                    return obj.type;
                };

                const ensureLayerMetadata = (obj, { newId = false } = {}) => {
                    if (!obj) {
                        return;
                    }
                    obj.data = obj.data && typeof obj.data === 'object' ? obj.data : {};
                    if (newId || !obj.data.id) {
                        obj.data.id = createLayerId();
                    }
                    if (!obj.data.name) {
                        obj.data.name = getDefaultLayerName(obj);
                    }
                };

                const getTextTarget = (obj) => {
                    if (!obj) {
                        return null;
                    }
                    if (obj.type === 'textbox' || obj.type === 'text') {
                        return obj;
                    }
                    if (obj.type === 'group') {
                        return obj.getObjects().find((child) => child.type === 'textbox' || child.type === 'text') || null;
                    }
                    return null;
                };

                const getActiveTextTarget = () => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return null;
                    }
                    return getTextTarget(active);
                };

                const syncTextTools = (textTarget = null) => {
                    const isTextTool = activeTool === 'text' || Boolean(textTarget);
                    if (panelShell) {
                        panelShell.classList.toggle('is-text-tool', isTextTool);
                    }
                    if (textToolbar) {
                        textToolbar.classList.toggle('is-disabled', !isTextTool);
                    }
                    textControls.forEach((control) => {
                        if ('disabled' in control) {
                            control.disabled = !isTextTool;
                        }
                    });
                    if (!isTextTool && textSpacingPanel) {
                        textSpacingPanel.hidden = true;
                    }
                };

                const applyTextStyles = (updates = {}) => {
                    const active = canvas.getActiveObject();
                    const target = getTextTarget(active);
                    if (!target) {
                        return;
                    }
                    target.set({ ...updates });
                    if (active?.type === 'group' && typeof active.addWithUpdate === 'function') {
                        active.addWithUpdate();
                    }
                    canvas.renderAll();
                    saveState();
                    updateBuilderEditor();
                };

                const colorToHex = (value) => {
                    if (!value || typeof value !== 'string') {
                        return '';
                    }
                    const color = value.trim();
                    if (color.startsWith('#')) {
                        if (color.length === 4) {
                            return `#${color[1]}${color[1]}${color[2]}${color[2]}${color[3]}${color[3]}`;
                        }
                        return color.slice(0, 7);
                    }
                    const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
                    if (!match) {
                        return '';
                    }
                    const toHex = (num) => Number(num).toString(16).padStart(2, '0');
                    return `#${toHex(match[1])}${toHex(match[2])}${toHex(match[3])}`;
                };

                const getDesignLayers = () =>
                    canvas.getObjects().filter((obj) => obj?.data?.role === 'design');

                const renderBuilderList = () => {
                    if (!builderList) {
                        return;
                    }
                    const designs = getDesignLayers();
                    builderList.innerHTML = '';
                    if (builderEmpty) {
                        builderEmpty.hidden = designs.length > 0;
                    }
                    const active = canvas.getActiveObject();
                    const ordered = designs.slice().reverse();
                    ordered.forEach((obj) => {
                        ensureLayerMetadata(obj);
                        const isActive = active === obj;
                        const isLocked = !!obj?.data?.locked;
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = `mockup-layer-item${isActive ? ' is-active' : ''}`;
                        item.dataset.layerId = obj.data.id;
                        item.innerHTML = `
                            <span class="mockup-layer-meta">
                                <span>${obj.data.name}</span>
                                <small>${getLayerTypeLabel(obj)}</small>
                            </span>
                            <span class="mockup-layer-state${isLocked ? ' is-locked' : ''}">
                                ${isLocked ? 'Locked' : 'Editable'}
                            </span>
                        `;
                        item.addEventListener('click', () => {
                            canvas.setActiveObject(obj);
                            canvas.renderAll();
                            updateBuilderEditor();
                            renderBuilderList();
                        });
                        builderList.appendChild(item);
                    });
                };

                const updateBuilderEditor = () => {
                    if (!builderEditor) {
                        return;
                    }
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        builderEditor.hidden = true;
                        syncTextTools(null);
                        return;
                    }
                    ensureLayerMetadata(active);
                    builderEditor.hidden = false;
                    if (builderNameInput) {
                        builderNameInput.value = active.data.name || '';
                    }
                    const textTarget = getTextTarget(active);
                    syncTextTools(textTarget);
                    if (builderTextRow) {
                        builderTextRow.hidden = !textTarget;
                    }
                    if (builderTextInput) {
                        builderTextInput.value = textTarget?.text || '';
                    }
                    if (textTarget) {
                        if (textColorInput) {
                            textColorInput.value = colorToHex(textTarget.fill) || '#111827';
                        }
                        if (textSizeInput) {
                            const size = Math.round(textTarget.fontSize || 36);
                            textSizeInput.value = size;
                        }
                        if (textFontSelect) {
                            textFontSelect.value = textTarget.fontFamily || textFontSelect.value;
                        }
                        if (textLineHeightInput) {
                            textLineHeightInput.value = textTarget.lineHeight ? String(textTarget.lineHeight) : '1.2';
                        }
                        if (textLetterSpacingInput) {
                            const spacing = Math.round(textTarget.charSpacing || 0);
                            textLetterSpacingInput.value = String(spacing);
                        }
                        const isBold = String(textTarget.fontWeight || '').toLowerCase() === 'bold' ||
                            Number(textTarget.fontWeight) >= 600;
                        const isItalic = String(textTarget.fontStyle || '').toLowerCase() === 'italic';
                        const isUnderline = Boolean(textTarget.underline);
                        const isStrike = Boolean(textTarget.linethrough);
                        textBoldBtn?.classList.toggle('is-active', isBold);
                        textItalicBtn?.classList.toggle('is-active', isItalic);
                        textUnderlineBtn?.classList.toggle('is-active', isUnderline);
                        textStrikeBtn?.classList.toggle('is-active', isStrike);
                        const alignValue = textTarget.textAlign || 'left';
                        textAlignBtns.forEach((btn) =>
                            btn.classList.toggle('is-active', btn.dataset.textAlign === alignValue)
                        );
                    }
                    if (builderColorInput) {
                        let color = active.fill;
                        if (!color && active.type === 'group') {
                            const child = active.getObjects().find((obj) => obj.fill);
                            color = child?.fill;
                        }
                        if (!color && active.stroke) {
                            color = active.stroke;
                        }
                        builderColorInput.value = colorToHex(color) || '#3b82f6';
                    }
                    if (builderOpacityInput) {
                        const value = Math.round((active.opacity ?? 1) * 100);
                        builderOpacityInput.value = value;
                        if (builderOpacityValue) {
                            builderOpacityValue.textContent = `${value}%`;
                        }
                    }
                    if (builderLockBtn) {
                        builderLockBtn.textContent = active?.data?.locked ? 'Unlock' : 'Lock';
                    }
                };

                const refreshBuilder = () => {
                    renderBuilderList();
                    updateBuilderEditor();
                };

                const renderSavedDesigns = () => {
                    if (!savedList) {
                        return;
                    }
                    const items = pruneSavedDesigns(getSavedDesigns());
                    saveDesignsToStorage(items);
                    savedList.innerHTML = '';
                    if (savedEmpty) {
                        savedEmpty.hidden = items.length > 0;
                    }
                    items.forEach((item) => {
                        const card = document.createElement('button');
                        card.type = 'button';
                        card.className = 'mockup-saved-card';
                        card.innerHTML = `
                            <button class="mockup-saved-delete" type="button" aria-label="Delete saved design">×</button>
                            <div class="mockup-saved-thumb" style="background-image:url('${item.preview || ''}')"></div>
                            <div class="mockup-saved-meta">
                                <span>${item.name || 'Saved design'}</span>
                                <small>${formatSavedTime(item.savedAt)}</small>
                            </div>
                        `;
                        const deleteBtn = card.querySelector('.mockup-saved-delete');
                        deleteBtn.addEventListener('click', (event) => {
                            event.stopPropagation();
                            const updated = pruneSavedDesigns(getSavedDesigns()).filter((entry) => entry.id !== item.id);
                            saveDesignsToStorage(updated);
                            renderSavedDesigns();
                        });
                        card.addEventListener('click', () => {
                            if (!item.snapshot) {
                                return;
                            }
                            const theme = item.bgTheme || 'light';
                            const config = mockupConfig[item.mockup] ? item.mockup : currentMockup;
                            currentMockup = config;
                            currentColorIndex = typeof item.colorIndex === 'number' ? item.colorIndex : 0;
                            currentView = resolveView(currentMockup, item.view || currentView);
                            currentPrintArea = scalePrintArea(mockupConfig[currentMockup].printArea);
                            canvas.clear();
                            history = [];
                            redoStack = [];
                            updateHistoryButtons();
                            renderViews();
                            canvas.loadFromJSON(item.snapshot, () => {
                                setBackgroundTheme(theme);
                                syncLayerRefs();
                                wirePrintAreaRect();
                                setLayerOrder();
                                canvas.renderAll();
                                renderColors();
                                updateHistoryButtons();
                                fitToContainer();
                                refreshBuilder();
                            });
                        });
                        savedList.appendChild(card);
                    });
                };

                const hexToRgb = (hex) => {
                    if (!hex) {
                        return null;
                    }
                    const clean = hex.replace('#', '');
                    if (clean.length !== 6) {
                        return null;
                    }
                    const num = parseInt(clean, 16);
                    return {
                        r: (num >> 16) & 255,
                        g: (num >> 8) & 255,
                        b: num & 255,
                    };
                };

                const rgba = (hex, alpha) => {
                    const rgb = hexToRgb(hex);
                    if (!rgb) {
                        return hex;
                    }
                    return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
                };

                const isNearWhite = (value) => {
                    const normalized = normalizeHex(value);
                    const rgb = hexToRgb(normalized);
                    if (!rgb) {
                        return false;
                    }
                    return (rgb.r + rgb.g + rgb.b) / 3 >= 235;
                };

                const resolveDefaultColorIndex = (colors) => {
                    if (!Array.isArray(colors) || colors.length === 0) {
                        return 0;
                    }
                    const byName = colors.findIndex((color) =>
                        /white/i.test(color?.name || '') || /^white$/i.test(color?.value || '')
                    );
                    if (byName !== -1) {
                        return byName;
                    }
                    const byAlt = colors.findIndex((color) => /ivory|cream|snow|off-white|offwhite/i.test(color?.name || ''));
                    if (byAlt !== -1) {
                        return byAlt;
                    }
                    const byHex = colors.findIndex((color) => isNearWhite(color?.value));
                    return byHex !== -1 ? byHex : 0;
                };

                const lighten = (hex, amount) => {
                    const raw = hex.replace('#', '');
                    const num = parseInt(raw, 16);
                    const r = Math.min(255, (num >> 16) + amount);
                    const g = Math.min(255, ((num >> 8) & 0xff) + amount);
                    const b = Math.min(255, (num & 0xff) + amount);
                    return `rgb(${r}, ${g}, ${b})`;
                };

                const mockupSvgs = {
                    tshirt: (color) => {
                        const highlight = lighten(color, 25);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <path d="M180 200 L280 140 L350 190 L470 190 L540 140 L640 200 L600 330 L560 310 L560 840 L260 840 L260 310 L220 330 Z"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8" />
                                <path d="M260 310 L560 310 L560 360 L260 360 Z" fill="${highlight}" opacity="0.35"/>
                                <path d="M220 330 L180 200 L150 220 L200 350 Z" fill="rgba(0,0,0,0.08)"/>
                                <path d="M640 200 L600 330 L620 350 L670 220 Z" fill="rgba(0,0,0,0.08)"/>
                            </svg>
                        `;
                    },
                    hoodie: (color) => {
                        const highlight = lighten(color, 30);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <path d="M250 160 C320 90 500 90 570 160 L640 260 L600 340 L560 320 L560 860 L260 860 L260 320 L220 340 L180 260 Z"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <path d="M300 200 C360 130 460 130 520 200 L520 260 L300 260 Z" fill="${highlight}" opacity="0.35"/>
                                <rect x="365" y="470" width="90" height="150" rx="40" fill="rgba(0,0,0,0.12)"/>
                            </svg>
                        `;
                    },
                    polo: (color) => {
                        const highlight = lighten(color, 20);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <path d="M210 210 L300 140 L520 140 L610 210 L580 300 L540 280 L540 820 L280 820 L280 280 L240 300 Z"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <path d="M360 150 L410 220 L460 150" fill="none" stroke="${highlight}" stroke-width="12"/>
                                <path d="M280 300 L540 300 L540 330 L280 330 Z" fill="${highlight}" opacity="0.3"/>
                            </svg>
                        `;
                    },
                    cap: (color) => {
                        const highlight = lighten(color, 20);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <path d="M240 420 C260 330 360 280 470 280 C590 280 690 340 690 430 L690 470 L240 470 Z"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <path d="M240 470 L690 470 L730 520 L200 520 Z" fill="${highlight}" opacity="0.4"/>
                                <path d="M240 420 C320 350 430 320 520 330" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="10"/>
                            </svg>
                        `;
                    },
                    mug: (color) => {
                        const highlight = lighten(color, 30);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <rect x="230" y="280" width="360" height="420" rx="40"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <rect x="260" y="320" width="300" height="80" rx="30" fill="${highlight}" opacity="0.3"/>
                                <path d="M590 360 C680 360 700 430 690 510 C680 580 610 590 590 560" fill="none"
                                      stroke="rgba(15,23,42,0.2)" stroke-width="28"/>
                            </svg>
                        `;
                    },
                    pillow: (color) => {
                        const highlight = lighten(color, 20);
                        return `
                            <svg width="600" height="600" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <rect x="190" y="240" width="440" height="460" rx="90"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <rect x="240" y="280" width="340" height="120" rx="60" fill="${highlight}" opacity="0.35"/>
                            </svg>
                        `;
                    },
                    totebag: (color) => {
                        const highlight = lighten(color, 18);
                        return `
                            <svg width="760" height="760" viewBox="0 0 820 980" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="820" height="980" fill="transparent"/>
                                <rect x="250" y="300" width="320" height="420" rx="26"
                                      fill="${color}" stroke="rgba(15,23,42,0.12)" stroke-width="8"/>
                                <rect x="270" y="320" width="280" height="80" rx="20" fill="${highlight}" opacity="0.35"/>
                                <path d="M300 300 C300 210 370 170 410 170 C450 170 520 210 520 300"
                                      fill="none" stroke="rgba(15,23,42,0.2)" stroke-width="18" stroke-linecap="round"/>
                            </svg>
                        `;
                    },
                };

                const resolveBackground = (theme) => {
                    if (!theme) {
                        return BG_THEMES.light;
                    }
                    return BG_THEMES[theme] || theme;
                };

                const updateBackgroundButtons = (theme) => {
                    document.querySelectorAll('[data-bg]').forEach((el) => {
                        el.classList.toggle('is-active', el.dataset.bg === theme);
                    });
                };

                const setCanvasBackground = (theme) => {
                    const value = resolveBackground(theme);
                    canvas.setBackgroundColor(value, canvas.renderAll.bind(canvas));
                };

                const setBackgroundTheme = (theme) => {
                    currentBgTheme = theme || 'light';
                    setCanvasBackground(currentBgTheme);
                    updateBackgroundButtons(currentBgTheme);
                };

                const cycleBackgroundTheme = () => {
                    const themes = Object.keys(BG_THEMES);
                    if (!themes.length) {
                        return;
                    }
                    const currentIndex = themes.indexOf(currentBgTheme);
                    const nextIndex = currentIndex === -1 ? 0 : (currentIndex + 1) % themes.length;
                    setBackgroundTheme(themes[nextIndex]);
                };

                let guidesVisible = true;
                const setGuidesVisible = (visible) => {
                    guidesVisible = visible;
                    if (printAreaRect) {
                        printAreaRect.visible = visible;
                        printAreaRect.selectable = visible;
                        printAreaRect.evented = visible;
                    }
                    if (safeAreaRect) {
                        safeAreaRect.visible = visible;
                    }
                    if (printAreaShadow) {
                        printAreaShadow.visible = visible;
                    }
                    canvas.renderAll();
                };

                const clearDesignObjects = () => {
                    const designs = canvas.getObjects().filter((obj) => obj?.data?.role === 'design');
                    designs.forEach((obj) => canvas.remove(obj));
                    canvas.discardActiveObject();
                    setLayerOrder();
                    canvas.renderAll();
                    saveState();
                    refreshBuilder();
                };

                const saveDesignCopy = () => {
                    const now = Date.now();
                    const name = `Copy ${new Date(now).toLocaleDateString()} ${new Date(now).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                    })}`;
                    const preview = canvas.toDataURL({
                        format: 'png',
                        multiplier: 0.4,
                        quality: 0.9,
                    });
                    const snapshot = canvas.toJSON(['data', 'clipPath', 'globalCompositeOperation']);
                    const items = pruneSavedDesigns(getSavedDesigns());
                    items.unshift({
                        id: `${now}-${Math.random().toString(16).slice(2)}`,
                        name,
                        preview,
                        snapshot,
                        savedAt: now,
                        mockup: currentMockup,
                        colorIndex: currentColorIndex,
                        view: currentView,
                        bgTheme: currentBgTheme || 'light',
                    });
                    saveDesignsToStorage(items);
                    renderSavedDesigns();
                };

                const syncLayerRefs = () => {
                    mockupImage = null;
                    printAreaRect = null;
                    safeAreaRect = null;
                    printAreaShadow = null;
                    canvas.getObjects().forEach((obj) => {
                        const role = obj?.data?.role;
                        if (role === 'mockup-base') {
                            mockupImage = obj;
                        } else if (role === 'print-area') {
                            printAreaRect = obj;
                        } else if (role === 'safe-area') {
                            safeAreaRect = obj;
                        } else if (role === 'print-shadow') {
                            printAreaShadow = obj;
                        }
                    });
                };

                const getLayerGroups = () => {
                    const allDesigns = canvas.getObjects().filter((obj) => obj?.data?.role === 'design');
                    const underlays = allDesigns.filter((obj) => obj?.data?.layer === 'underlay');
                    const designs = allDesigns.filter((obj) => obj?.data?.layer !== 'underlay');
                    return { underlays, designs };
                };

                const setLayerOrder = () => {
                    const { underlays, designs } = getLayerGroups();
                    const overlays = [printAreaShadow, printAreaRect, safeAreaRect].filter(Boolean);
                    const ordered = [];
                    ordered.push(...underlays);
                    if (mockupImage) {
                        ordered.push(mockupImage);
                    }
                    ordered.push(...designs);
                    ordered.push(...overlays);
                    ordered.filter(Boolean).forEach((obj, index) => canvas.moveTo(obj, index));
                };

                const createClipPath = () => new fabric.Rect({
                    left: currentPrintArea.x,
                    top: currentPrintArea.y,
                    width: currentPrintArea.w,
                    height: currentPrintArea.h,
                    absolutePositioned: true,
                });

                const getViewKey = (mockupKey, view) => `${view}`;

                const captureViewDesigns = () => {
                    const key = getViewKey(currentMockup, currentView);
                    const designs = getDesignLayers();
                    if (!designs.length) {
                        delete viewDesigns[key];
                        return;
                    }
                    viewDesigns[key] = designs.map((obj) =>
                        obj.toObject(['data', 'clipPath', 'globalCompositeOperation'])
                    );
                };

                const restoreViewDesigns = (mockupKey, view, done = () => {}) => {
                    const key = getViewKey(mockupKey, view);
                    const snapshot = viewDesigns[key];
                    if (!snapshot || snapshot.length === 0) {
                        done();
                        return;
                    }
                    fabric.util.enlivenObjects(snapshot, (enlivened) => {
                        enlivened.forEach((obj) => {
                            ensureLayerMetadata(obj);
                            obj.data = { ...(obj.data || {}), role: 'design' };
                            obj.clipPath = createClipPath();
                            obj.set({
                                selectable: true,
                                evented: true,
                            });
                            canvas.add(obj);
                        });
                        done();
                    });
                };

                const syncPrintAreaFromRect = (commit = false) => {
                    if (!printAreaRect) {
                        return;
                    }
                    const scaledWidth = printAreaRect.width * printAreaRect.scaleX;
                    const scaledHeight = printAreaRect.height * printAreaRect.scaleY;
                    const left = printAreaRect.left ?? 0;
                    const top = printAreaRect.top ?? 0;
                    printAreaRect.set({
                        width: scaledWidth,
                        height: scaledHeight,
                        scaleX: 1,
                        scaleY: 1,
                    });
                    currentPrintArea = { x: left, y: top, w: scaledWidth, h: scaledHeight };
                    if (safeAreaRect) {
                        safeAreaRect.set({
                            left: left + scaledWidth * 0.08,
                            top: top + scaledHeight * 0.08,
                            width: scaledWidth * 0.84,
                            height: scaledHeight * 0.84,
                        });
                        safeAreaRect.setCoords();
                    }
                    printAreaRect.setCoords();
                    canvas.getObjects().forEach((obj) => {
                        if (obj?.data?.role === 'design') {
                            obj.clipPath = createClipPath();
                        }
                    });
                    canvas.renderAll();
                    if (commit) {
                        saveState();
                    }
                };

                const wirePrintAreaRect = () => {
                    if (!printAreaRect) {
                        return;
                    }
                    if (!safeAreaRect) {
                        safeAreaRect = new fabric.Rect({
                            left: currentPrintArea.x + currentPrintArea.w * 0.08,
                            top: currentPrintArea.y + currentPrintArea.h * 0.08,
                            width: currentPrintArea.w * 0.84,
                            height: currentPrintArea.h * 0.84,
                            fill: 'transparent',
                            stroke: 'rgba(15, 23, 42, 0.22)',
                            strokeDashArray: GUIDE_SAFE_DASH,
                            strokeWidth: GUIDE_SAFE_STROKE,
                            strokeUniform: false,
                            selectable: false,
                            evented: false,
                            data: { role: 'safe-area' },
                        });
                        canvas.add(safeAreaRect);
                    }
                    printAreaRect.set({
                        fill: 'transparent',
                        stroke: 'rgba(59, 130, 246, 0.45)',
                        strokeDashArray: GUIDE_PRINT_DASH,
                        strokeWidth: GUIDE_PRINT_STROKE,
                        strokeUniform: false,
                        perPixelTargetFind: true,
                        targetFindTolerance: 4,
                        selectable: true,
                        evented: true,
                        hasControls: true,
                        hasBorders: true,
                        lockRotation: true,
                    });
                    safeAreaRect.set({
                        fill: 'transparent',
                        stroke: 'rgba(15, 23, 42, 0.22)',
                        strokeDashArray: GUIDE_SAFE_DASH,
                        strokeWidth: GUIDE_SAFE_STROKE,
                        strokeUniform: false,
                        selectable: false,
                        evented: false,
                    });
                    printAreaRect.off('moving');
                    printAreaRect.off('scaling');
                    printAreaRect.off('modified');
                    printAreaRect.on('moving', () => syncPrintAreaFromRect(false));
                    printAreaRect.on('scaling', () => syncPrintAreaFromRect(false));
                    printAreaRect.on('modified', () => syncPrintAreaFromRect(true));
                    syncPrintAreaFromRect(false);
                };

                const renderPrintArea = () => {
                    const { x, y, w, h } = currentPrintArea;
                    printAreaRect = new fabric.Rect({
                        left: x,
                        top: y,
                        width: w,
                        height: h,
                        fill: 'transparent',
                        stroke: 'rgba(59, 130, 246, 0.45)',
                        strokeDashArray: GUIDE_PRINT_DASH,
                        strokeWidth: GUIDE_PRINT_STROKE,
                        strokeUniform: false,
                        perPixelTargetFind: true,
                        targetFindTolerance: 4,
                        selectable: true,
                        evented: true,
                        hasControls: true,
                        hasBorders: true,
                        lockRotation: true,
                        data: { role: 'print-area' },
                    });

                    safeAreaRect = new fabric.Rect({
                        left: x + w * 0.08,
                        top: y + h * 0.08,
                        width: w * 0.84,
                        height: h * 0.84,
                        fill: 'transparent',
                        stroke: 'rgba(15, 23, 42, 0.22)',
                        strokeDashArray: GUIDE_SAFE_DASH,
                        strokeWidth: GUIDE_SAFE_STROKE,
                        strokeUniform: false,
                        selectable: false,
                        evented: false,
                        data: { role: 'safe-area' },
                    });

                    const gradient = new fabric.Gradient({
                        type: 'linear',
                        coords: { x1: x, y1: y, x2: x + w, y2: y + h },
                        colorStops: [
                            { offset: 0, color: 'rgba(0, 0, 0, 0.18)' },
                            { offset: 0.5, color: 'rgba(0, 0, 0, 0.03)' },
                            { offset: 1, color: 'rgba(0, 0, 0, 0.2)' },
                        ],
                    });

                    printAreaShadow = new fabric.Rect({
                        left: x,
                        top: y,
                        width: w,
                        height: h,
                        fill: 'transparent',
                        selectable: false,
                        evented: false,
                        data: { role: 'print-shadow' },
                    });

                    canvas.add(printAreaRect, safeAreaRect);
                    wirePrintAreaRect();
                };

                const loadMockup = (mockupKey, colorIndex = 0, view = null) => {
                    captureViewDesigns();
                    currentMockup = mockupKey;
                    const colors = getProductColors(mockupKey);
                    const fallbackIndex = resolveDefaultColorIndex(colors);
                    const desiredIndex = Number.isInteger(colorIndex) ? colorIndex : fallbackIndex;
                    const safeIndex = Math.max(0, Math.min(desiredIndex, Math.max(colors.length - 1, 0)));
                    currentColorIndex = safeIndex;
                    currentView = resolveView(mockupKey, view || currentView);
                    renderViews();
                    history = [];
                    redoStack = [];
                    updateHistoryButtons();
                    const config = mockupConfig[mockupKey];
                    currentPrintArea = scalePrintArea(config.printArea);
                    const color = colors[safeIndex]?.value || '#f9fafb';
                    const svg = mockupSvgs[mockupKey](color);
                    const fallbackUrl = toDataUrl(svg);
                    const imageUrl = getMockupImageUrl(mockupKey, currentView, color);
                    const url = imageUrl || fallbackUrl;

                    canvas.clear();
                    setBackgroundTheme(color);

                    fabric.Image.fromURL(url, (img) => {
                        let scaleX = 1;
                        let scaleY = 1;
                        let left = 0;
                        let top = 0;
                        let originX = 'left';
                        let originY = 'top';
                        if (img.width > 0 && img.height > 0) {
                            const scale = Math.min(CANVAS_WIDTH / img.width, CANVAS_HEIGHT / img.height);
                            scaleX = scale;
                            scaleY = scale;
                            left = CANVAS_WIDTH / 2;
                            top = CANVAS_HEIGHT / 2;
                            originX = 'center';
                            originY = 'center';
                        }
                        img.set({
                            left,
                            top,
                            originX,
                            originY,
                            scaleX,
                            scaleY,
                            selectable: false,
                            evented: false,
                            data: { role: 'mockup-base' },
                        });
                        mockupImage = img;
                        canvas.add(img);
                        renderPrintArea();
                        setGuidesVisible(guidesVisible);
                        if (printAreaRect) {
                            canvas.setActiveObject(printAreaRect);
                        }
                        restoreViewDesigns(currentMockup, currentView, () => {
                            setLayerOrder();
                            saveState();
                            fitToContainer();
                            refreshBuilder();
                        });
                    }, { crossOrigin: 'anonymous' });
                };

                const resolveCanvasViewport = (containerOverride = null) => {
                    const wrap = document.querySelector('[data-canvas-wrap]');
                    const artboard = document.querySelector('.mockup-artboard');
                    const elements = [containerOverride, wrap, artboard].filter(Boolean);
                    const widths = elements
                        .map((element) => element.clientWidth)
                        .filter((size) => Number.isFinite(size) && size > 0);
                    const heights = elements
                        .map((element) => element.clientHeight)
                        .filter((size) => Number.isFinite(size) && size > 0);

                    return {
                        width: widths.length ? Math.min(...widths) : CANVAS_WIDTH,
                        height: heights.length ? Math.min(...heights) : CANVAS_HEIGHT,
                    };
                };

                const fitToContainer = () => {
                    const { width, height } = resolveCanvasViewport();
                    if (!width || !height) {
                        return;
                    }
                    const fitRatio = Math.min(width / CANVAS_WIDTH, height / CANVAS_HEIGHT, 1);
                    const fitPercent = Math.max(25, Math.round(fitRatio * 100));
                    setZoom(fitPercent);
                };

                const setZoom = (value, containerOverride = null) => {
                    const zoom = Math.max(0.25, Math.min(2, value / 100));
                    canvas.setZoom(zoom);
                    const viewport = resolveCanvasViewport(containerOverride);
                    const view = canvas.viewportTransform;
                    if (view) {
                        const offsetX = (viewport.width - CANVAS_WIDTH * zoom) / 2;
                        const offsetY = (viewport.height - CANVAS_HEIGHT * zoom) / 2;
                        view[4] = Number.isFinite(offsetX) ? offsetX : 0;
                        view[5] = Number.isFinite(offsetY) ? offsetY : 0;
                        canvas.setViewportTransform(view);
                    }
                    zoomRange.value = Math.round(zoom * 100);
                    zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
                };

                const updateHistoryButtons = () => {
                    undoBtn.disabled = history.length <= 1;
                    redoBtn.disabled = redoStack.length === 0;
                };

                const saveState = () => {
                    if (isRestoring) {
                        return;
                    }
                    const snapshot = getDesignLayers().map((obj) =>
                        obj.toObject(['data', 'clipPath', 'globalCompositeOperation'])
                    );
                    history.push(snapshot);
                    if (history.length > MAX_HISTORY) {
                        history.shift();
                    }
                    redoStack = [];
                    updateHistoryButtons();
                };

                const saveDesign = () => {
                    const now = Date.now();
                    const name = `Design ${new Date(now).toLocaleDateString()} ${new Date(now).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                    })}`;
                    const preview = canvas.toDataURL({
                        format: 'png',
                        multiplier: 0.4,
                        quality: 0.9,
                    });
                    const snapshot = canvas.toJSON(['data', 'clipPath', 'globalCompositeOperation']);
                    const bgTheme = currentBgTheme || 'light';
                    const items = pruneSavedDesigns(getSavedDesigns());
                    items.unshift({
                        id: `${now}-${Math.random().toString(16).slice(2)}`,
                        name,
                        preview,
                        snapshot,
                        savedAt: now,
                        mockup: currentMockup,
                        colorIndex: currentColorIndex,
                        view: currentView,
                        bgTheme,
                    });
                    saveDesignsToStorage(items);
                    renderSavedDesigns();
                };

                const showContextMenu = (x, y, target) => {
                    if (!contextMenu) {
                        return;
                    }
                    const isLocked = Boolean(target?.lockMovementX || target?.data?.locked);
                    const hasSelection = Boolean(target);
                    if (contextLockLabel) {
                        contextLockLabel.textContent = isLocked ? 'Unlock' : 'Lock';
                    }
                    contextItems.forEach((item) => {
                        if (!item.hasAttribute('data-requires-selection')) {
                            return;
                        }
                        if (hasSelection) {
                            item.removeAttribute('disabled');
                            item.classList.remove('is-disabled');
                        } else {
                            item.setAttribute('disabled', 'true');
                            item.classList.add('is-disabled');
                        }
                    });
                    contextMenu.style.left = `${x}px`;
                    contextMenu.style.top = `${y}px`;
                    contextMenu.classList.add('is-open');
                };

                const hideContextMenu = () => {
                    contextMenu?.classList.remove('is-open');
                };

                const toggleFileMenu = () => {
                    if (!fileMenu) {
                        return;
                    }
                    const isOpen = fileMenu.classList.toggle('is-open');
                    fileMenu.hidden = !isOpen;
                };

                const hideFileMenu = () => {
                    if (fileMenu) {
                        fileMenu.classList.remove('is-open');
                        fileMenu.hidden = true;
                    }
                };

                const updateLockedState = (active) => {
                    const locked = !active?.data?.locked;
                    active?.set({
                        lockMovementX: locked,
                        lockMovementY: locked,
                        lockScalingX: locked,
                        lockScalingY: locked,
                        lockRotation: locked,
                        hasControls: !locked,
                        selectable: !locked,
                        evented: !locked,
                        data: { ...(active?.data || {}), locked },
                    });
                    canvas.renderAll();
                    saveState();
                    refreshBuilder();
                };

                const alignActive = (mode) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    const { x, y, w, h } = currentPrintArea;
                    if (mode === 'left') {
                        active.set({ left: x, originX: 'left' });
                    }
                    if (mode === 'center') {
                        active.set({ left: x + w / 2, originX: 'center' });
                    }
                    if (mode === 'right') {
                        active.set({ left: x + w, originX: 'right' });
                    }
                    if (mode === 'top') {
                        active.set({ top: y, originY: 'top' });
                    }
                    if (mode === 'middle') {
                        active.set({ top: y + h / 2, originY: 'center' });
                    }
                    if (mode === 'bottom') {
                        active.set({ top: y + h, originY: 'bottom' });
                    }
                    active.setCoords();
                    canvas.renderAll();
                    saveState();
                };

                const copyActive = () => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }
                    active.clone((cloned) => {
                        clipboard = cloned;
                    });
                };

                const pasteActive = () => {
                    if (!clipboard) {
                        return;
                    }
                    clipboard.clone((cloned) => {
                        ensureLayerMetadata(cloned, { newId: true });
                        if (cloned.data?.name && !cloned.data.name.toLowerCase().includes('copy')) {
                            cloned.data.name = `${cloned.data.name} copy`;
                        }
                        cloned.set({
                            left: (cloned.left || currentPrintArea.x) + 20,
                            top: (cloned.top || currentPrintArea.y) + 20,
                            data: { ...(cloned.data || {}), role: 'design' },
                        });
                        cloned.clipPath = createClipPath();
                        canvas.add(cloned);
                        canvas.setActiveObject(cloned);
                        setLayerOrder();
                        canvas.renderAll();
                        saveState();
                        refreshBuilder();
                    });
                };

                const downloadSelection = () => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }
                    const bounds = active.getBoundingRect(true, true);
                    active.clone((cloned) => {
                        const tempCanvas = new fabric.StaticCanvas(null, {
                            width: Math.ceil(bounds.width),
                            height: Math.ceil(bounds.height),
                        });
                        cloned.set({
                            left: (cloned.left || 0) - bounds.left,
                            top: (cloned.top || 0) - bounds.top,
                        });
                        tempCanvas.add(cloned);
                        tempCanvas.renderAll();
                        const dataUrl = tempCanvas.toDataURL({ format: 'png', multiplier: 2 });
                        const link = document.createElement('a');
                        link.href = dataUrl;
                        link.download = 'selection.png';
                        link.click();
                    });
                };

                const applyColorsToPage = () => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }
                    let fill = active.fill;
                    if (!fill && active.type === 'group') {
                        const child = active.getObjects().find((obj) => obj.fill);
                        fill = child?.fill;
                    }
                    if (fill) {
                        canvas.setBackgroundColor(fill, canvas.renderAll.bind(canvas));
                    }
                };

                const restoreState = (json) => {
                    isRestoring = true;
                    const designs = getDesignLayers();
                    designs.forEach((obj) => canvas.remove(obj));
                    canvas.discardActiveObject();
                    if (!json || !json.length) {
                        setLayerOrder();
                        canvas.renderAll();
                        isRestoring = false;
                        refreshBuilder();
                        return;
                    }
                    fabric.util.enlivenObjects(json, (enlivened) => {
                        enlivened.forEach((obj) => {
                            ensureLayerMetadata(obj);
                            obj.data = { ...(obj.data || {}), role: 'design' };
                            const locked = Boolean(obj.data?.locked);
                            obj.clipPath = createClipPath();
                            obj.set({
                                selectable: !locked,
                                evented: !locked,
                                hasControls: !locked,
                                lockMovementX: locked,
                                lockMovementY: locked,
                                lockScalingX: locked,
                                lockScalingY: locked,
                                lockRotation: locked,
                            });
                            canvas.add(obj);
                        });
                        setLayerOrder();
                        canvas.renderAll();
                        isRestoring = false;
                        refreshBuilder();
                    });
                };

                const undo = () => {
                    if (history.length <= 1) {
                        return;
                    }
                    const current = history.pop();
                    redoStack.push(current);
                    const previous = history[history.length - 1];
                    restoreState(previous);
                    updateHistoryButtons();
                };

                const redo = () => {
                    if (!redoStack.length) {
                        return;
                    }
                    const next = redoStack.pop();
                    history.push(next);
                    restoreState(next);
                    updateHistoryButtons();
                };

                const addDesignObject = (obj, options = {}) => {
                    const { x, y, w, h } = currentPrintArea;
                    const maxScale = Math.min(w / obj.width, h / obj.height) * 0.8;
                    ensureLayerMetadata(obj, { newId: true });
                    const layer = options.layer || obj?.data?.layer || 'design';
                    obj.set({
                        left: x + w / 2,
                        top: y + h / 2,
                        originX: 'center',
                        originY: 'center',
                        scaleX: maxScale,
                        scaleY: maxScale,
                        lockUniScaling: true,
                        data: { ...(obj.data || {}), ...(options.data || {}), role: 'design', layer },
                    });
                    obj.clipPath = createClipPath();
                    canvas.add(obj);
                    canvas.setActiveObject(obj);
                    setLayerOrder();
                    canvas.renderAll();
                    saveState();
                    refreshBuilder();
                };

                const addTextTool = () => {
                    const textbox = new fabric.Textbox('Your text', {
                        width: 320,
                        fontSize: 44,
                        fontWeight: 600,
                        fontFamily: '"Plus Jakarta Sans", "Segoe UI", sans-serif',
                        fill: activeColor,
                    });
                    addDesignObject(textbox);
                };

                const addShapeTool = () => {
                    const rect = new fabric.Rect({
                        width: 320,
                        height: 220,
                        rx: 18,
                        ry: 18,
                        fill: activeColor,
                    });
                    addDesignObject(rect);
                };

                const addLineTool = () => {
                    const lineRect = new fabric.Rect({
                        width: 320,
                        height: 8,
                        rx: 4,
                        ry: 4,
                        fill: activeColor,
                    });
                    addDesignObject(lineRect);
                };

                const addTableTool = () => {
                    const width = 320;
                    const height = 220;
                    const stroke = activeColor;
                    const border = new fabric.Rect({
                        left: 0,
                        top: 0,
                        width,
                        height,
                        fill: 'transparent',
                        stroke,
                        strokeWidth: 3,
                    });
                    const v1 = new fabric.Rect({
                        left: width / 3,
                        top: 0,
                        width: 2,
                        height,
                        fill: stroke,
                    });
                    const v2 = new fabric.Rect({
                        left: (2 * width) / 3,
                        top: 0,
                        width: 2,
                        height,
                        fill: stroke,
                    });
                    const h1 = new fabric.Rect({
                        left: 0,
                        top: height / 3,
                        width,
                        height: 2,
                        fill: stroke,
                    });
                    const h2 = new fabric.Rect({
                        left: 0,
                        top: (2 * height) / 3,
                        width,
                        height: 2,
                        fill: stroke,
                    });
                    const table = new fabric.Group([border, v1, v2, h1, h2]);
                    addDesignObject(table);
                };

                const addStickyTool = () => {
                    const width = 320;
                    const height = 220;
                    const note = new fabric.Rect({
                        left: 0,
                        top: 0,
                        width,
                        height,
                        rx: 18,
                        ry: 18,
                        fill: rgba(activeColor, 0.2),
                        stroke: rgba(activeColor, 0.6),
                        strokeWidth: 2,
                    });
                    const text = new fabric.Textbox('Sticky note', {
                        left: 20,
                        top: 20,
                        width: width - 40,
                        fontSize: 28,
                        fontWeight: 600,
                        fontFamily: '"Plus Jakarta Sans", "Segoe UI", sans-serif',
                        fill: '#0f172a',
                    });
                    const noteGroup = new fabric.Group([note, text]);
                    addDesignObject(noteGroup);
                };

                const setActiveTool = (tool) => {
                    activeTool = tool;
                    toolButtons.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.tool === tool));
                    setToolsMenuOpen(tool === 'text');
                    canvas.isDrawingMode = false;
                    canvas.selection = tool === 'select';
                    syncTextTools(getActiveTextTarget());
                };

                const setMockupTab = (tab, options = {}) => {
                    if (!mockupTabs.length || !mockupTabSections.length) {
                        return;
                    }
                    const { persist = true } = options;
                    const normalizedTab = (tab || 'all').toLowerCase().trim();
                    const requestedTab = MOCKUP_TAB_KEYS.includes(normalizedTab) ? normalizedTab : activeMockupTab;
                    const resolvedTab = activePanel === 'uploads' ? 'designs' : requestedTab;

                    if (activePanel !== 'uploads') {
                        activeMockupTab = requestedTab;
                        if (persist) {
                            saveMockupTab(activeMockupTab);
                        }
                    }

                    if (!['mockups', 'uploads'].includes(activePanel)) {
                        return;
                    }

                    mockupTabs.forEach((btn) =>
                        btn.classList.toggle('is-active', btn.dataset.mockupTab === resolvedTab)
                    );
                    mockupTabSections.forEach((section) => {
                        const allowed = (section.dataset.mockupTabSection || '')
                            .split(' ')
                            .map((item) => item.trim())
                            .filter(Boolean)
                            .map((item) => item.toLowerCase());
                        const isVisible = allowed.includes(resolvedTab);
                        section.hidden = false;
                        section.style.display = isVisible ? '' : 'none';
                    });

                    if (resolvedTab === 'designs') {
                        renderSavedDesigns();
                    }
                };

                const setActivePanel = (panel) => {
                    const normalizedPanel = (panel || 'mockups').toLowerCase().trim();
                    const panelKeys = ['mockups', 'templates', 'apps', 'uploads', 'edit', 'qr', 'voice'];
                    const resolvedPanel = panelKeys.includes(normalizedPanel) ? normalizedPanel : 'mockups';
                    activePanel = resolvedPanel;

                    railButtons.forEach((btn) =>
                        btn.classList.toggle('is-active', btn.dataset.panel === resolvedPanel)
                    );
                    panelSections.forEach((section) => {
                        const allowed = (section.dataset.panelSection || '')
                            .split(' ')
                            .map((item) => item.trim())
                            .filter(Boolean)
                            .map((item) => item.toLowerCase());
                        const isVisible = allowed.includes(resolvedPanel);
                        section.hidden = false;
                        section.style.display = isVisible ? '' : 'none';
                    });

                    if (panelShell) {
                        panelShell.classList.remove(
                            'is-mockups',
                            'is-apps',
                            'is-uploads',
                            'is-edit',
                            'is-qr',
                            'is-voice'
                        );
                        panelShell.classList.add(`is-${resolvedPanel}`);
                        panelShell.scrollTop = 0;
                    }

                    if (resolvedPanel === 'uploads') {
                        setMockupTab(activeMockupTab, { persist: false });
                    } else if (resolvedPanel === 'mockups') {
                        setMockupTab(activeMockupTab, { persist: false });
                    }
                };

                const applyColorToActive = (color) => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }

                    const applyToObject = (obj) => {
                        if (obj.type === 'textbox' || obj.type === 'text') {
                            obj.set({ fill: color });
                            return;
                        }
                        if (obj.fill && obj.fill !== 'transparent') {
                            obj.set({ fill: color });
                        }
                        if (obj.stroke) {
                            obj.set({ stroke: color });
                        }
                    };

                    if (active.type === 'group') {
                        active.getObjects().forEach(applyToObject);
                    } else {
                        applyToObject(active);
                    }

                    canvas.renderAll();
                    saveState();
                    refreshBuilder();
                };

                const addUpload = (file) => {
                    if (!file) {
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const dataUrl = event.target?.result;
                        if (!dataUrl) {
                            return;
                        }
                        uploads.unshift({
                            id: `${Date.now()}-${Math.random()}`,
                            name: file.name,
                            url: dataUrl,
                            mimeType: (file.type || '').toLowerCase(),
                        });
                        renderUploads();
                        placeUpload(dataUrl, { source: 'uploads', name: file.name, mimeType: file.type });
                    };
                    reader.readAsDataURL(file);
                };

                const inferMimeTypeFromSource = (source = '') => {
                    if (typeof source !== 'string' || !source) {
                        return '';
                    }
                    const dataMatch = source.match(/^data:([^;,]+)[;,]/i);
                    if (dataMatch?.[1]) {
                        return dataMatch[1].toLowerCase();
                    }
                    const path = source.split('?')[0].toLowerCase();
                    if (path.endsWith('.jpg') || path.endsWith('.jpeg')) {
                        return 'image/jpeg';
                    }
                    if (path.endsWith('.png')) {
                        return 'image/png';
                    }
                    if (path.endsWith('.svg')) {
                        return 'image/svg+xml';
                    }
                    return '';
                };

                const placeUpload = (dataUrl, metadata = {}) => {
                    const inferredMime = (metadata.mimeType || inferMimeTypeFromSource(dataUrl) || '').toLowerCase();
                    const imageData = {
                        source: metadata.source || 'upload',
                        sourceName: metadata.name || '',
                        sourceMime: inferredMime,
                    };
                    if (dataUrl.startsWith('data:image/svg')) {
                        fabric.loadSVGFromURL(dataUrl, (objects, options) => {
                            const group = fabric.util.groupSVGElements(objects, options);
                            addDesignObject(group, { layer: 'underlay', data: imageData });
                        });
                    } else {
                        fabric.Image.fromURL(dataUrl, (img) => addDesignObject(img, { layer: 'underlay', data: imageData }));
                    }
                };

                const renderUploads = () => {
                    uploadList.innerHTML = '';
                    uploads.forEach((upload) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'mockup-upload-thumb';
                        btn.style.backgroundImage = `url('${upload.url}')`;
                        btn.title = upload.name;
                        btn.addEventListener('click', () =>
                            placeUpload(upload.url, {
                                source: 'uploads',
                                name: upload.name,
                                mimeType: upload.mimeType,
                            })
                        );
                        uploadList.appendChild(btn);
                    });
                };

                const renderEditColors = (colors) => {
                    if (!editColorRow) {
                        return;
                    }
                    editColorRow.innerHTML = '';
                    const hasActive = colors.some((entry) => entry.value === activeColor);
                    if ((!activeColor || !hasActive) && colors.length) {
                        activeColor = colors[0].value;
                    }
                    if (colorRing) {
                        colorRing.style.background = activeColor || '#ffffff';
                        colorRing.style.borderColor = activeColor || '#2563eb';
                        colorRing.style.boxShadow = `inset 0 0 0 1px rgba(37, 99, 235, 0.12), 0 0 0 2px rgba(37, 99, 235, 0.1)`;
                    }
                    colors.forEach((color) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = `mockup-color-dot ${color.value === activeColor ? 'is-active' : ''}`;
                        btn.dataset.color = color.value;
                        btn.style.background = color.value;
                        btn.title = color.name;
                        btn.addEventListener('click', () => {
                            activeColor = color.value;
                            if (colorRing) {
                                colorRing.style.background = color.value;
                                colorRing.style.borderColor = color.value;
                            }
                            editColorRow.querySelectorAll('.mockup-color-dot').forEach((node) => {
                                node.classList.toggle('is-active', node === btn);
                            });
                            applyColorToActive(color.value);
                        });
                        editColorRow.appendChild(btn);
                    });
                };

                const updateViewNavButtons = (views) => {
                    const disabled = !views || views.length < 2;
                    const activeView = (currentView || '').toLowerCase();
                    viewNavButtons.forEach((btn) => {
                        btn.disabled = disabled;
                        const dir = btn.dataset.viewNav;
                        const isActive =
                            !disabled &&
                            ((dir === 'prev' && activeView === 'front') || (dir === 'next' && activeView === 'back'));
                        btn.classList.toggle('is-active', isActive);
                    });
                };

                const navigateView = (direction) => {
                    const views = getMockupViews(currentMockup);
                    if (!views.length || views.length < 2) {
                        return;
                    }
                    const currentIndex = Math.max(0, views.indexOf(currentView));
                    const nextIndex = (currentIndex + direction + views.length) % views.length;
                    const nextView = views[nextIndex];
                    if (nextView === currentView) {
                        return;
                    }
                    loadMockup(currentMockup, currentColorIndex, nextView);
                };

                const renderViews = () => {
                    if (!viewRow) {
                        return;
                    }
                    viewRow.innerHTML = '';
                    const views = getMockupViews(currentMockup);
                    views.forEach((view) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = `mockup-view-btn ${view === currentView ? 'is-active' : ''}`;
                        btn.textContent = viewLabels[view] || view;
                        btn.addEventListener('click', () => {
                            if (view === currentView) {
                                return;
                            }
                            loadMockup(currentMockup, currentColorIndex, view);
                        });
                        viewRow.appendChild(btn);
                    });
                    updateViewNavButtons(views);
                };

                const renderColors = () => {
                    colorRow.innerHTML = '';
                    const colors = getProductColors(currentMockup);
                    renderEditColors(colors);
                    colors.forEach((color, index) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = `mockup-color ${index === currentColorIndex ? 'is-active' : ''}`;
                        btn.innerHTML = `<span style="background:${color.value}"></span><small>${color.name}</small>`;
                        btn.addEventListener('click', () => {
                            document.querySelectorAll('.mockup-color').forEach((node) => node.classList.remove('is-active'));
                            btn.classList.add('is-active');
                            loadMockup(currentMockup, index, currentView);
                        });
                        colorRow.appendChild(btn);
                    });
                };


                const alignObject = (axis) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    const { x, y, w, h } = currentPrintArea;
                    if (axis === 'center-x') {
                        active.set({ left: x + w / 2, originX: 'center' });
                    }
                    if (axis === 'center-y') {
                        active.set({ top: y + h / 2, originY: 'center' });
                    }
                    active.setCoords();
                    canvas.renderAll();
                    saveState();
                };

                const updateOpacity = (value) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    active.set({ opacity: value / 100 });
                    canvas.renderAll();
                    saveState();
                    if (builderOpacityInput) {
                        builderOpacityInput.value = value;
                    }
                    if (builderOpacityValue) {
                        builderOpacityValue.textContent = `${value}%`;
                    }
                };

                const flipActive = (axis) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    if (axis === 'x') {
                        active.set({ flipX: !active.flipX });
                    } else {
                        active.set({ flipY: !active.flipY });
                    }
                    canvas.renderAll();
                    saveState();
                };

                const deleteActive = () => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    canvas.remove(active);
                    canvas.discardActiveObject();
                    canvas.renderAll();
                    saveState();
                    refreshBuilder();
                };

                const moveLayer = (direction) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    const { underlays, designs } = getLayerGroups();
                    const isUnderlay = active?.data?.layer === 'underlay';
                    const group = isUnderlay ? underlays : designs;
                    const index = group.indexOf(active);
                    if (index < 0) {
                        return;
                    }
                    const targetIndex = direction === 'forward' ? index + 1 : index - 1;
                    if (targetIndex < 0 || targetIndex >= group.length) {
                        return;
                    }
                    const temp = group[index];
                    group[index] = group[targetIndex];
                    group[targetIndex] = temp;
                    const overlays = [printAreaShadow, printAreaRect, safeAreaRect].filter(Boolean);
                    const ordered = [...underlays, mockupImage, ...designs, ...overlays].filter(Boolean);
                    ordered.forEach((obj, idx) => canvas.moveTo(obj, idx));
                    canvas.renderAll();
                    saveState();
                };

                const moveLayerToEdge = (direction) => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        return;
                    }
                    const { underlays, designs } = getLayerGroups();
                    const isUnderlay = active?.data?.layer === 'underlay';
                    const group = isUnderlay ? underlays : designs;
                    const index = group.indexOf(active);
                    if (index < 0) {
                        return;
                    }
                    group.splice(index, 1);
                    if (direction === 'front') {
                        group.push(active);
                    } else {
                        group.unshift(active);
                    }
                    const overlays = [printAreaShadow, printAreaRect, safeAreaRect].filter(Boolean);
                    const ordered = [...underlays, mockupImage, ...designs, ...overlays].filter(Boolean);
                    ordered.forEach((obj, idx) => canvas.moveTo(obj, idx));
                    canvas.renderAll();
                    saveState();
                };

                const exportCanvas = (format) => {
                    const includeBg = exportBgToggle.checked;
                    const prevBg = canvas.backgroundColor;
                    const prevGuides = guidesVisible;
                    setGuidesVisible(false);
                    if (!includeBg) {
                        canvas.setBackgroundColor(null, canvas.renderAll.bind(canvas));
                    }
                    const dataUrl = canvas.toDataURL({
                        format,
                        multiplier: 3,
                        quality: format === 'jpeg' ? 0.92 : 1,
                    });
                    if (!includeBg) {
                        canvas.setBackgroundColor(prevBg, canvas.renderAll.bind(canvas));
                    }
                    if (prevGuides) {
                        setGuidesVisible(true);
                    }
                    const link = document.createElement('a');
                    link.href = dataUrl;
                    link.download = `nai-mockup-${currentMockup}.${format === 'jpeg' ? 'jpg' : format}`;
                    link.click();
                };

                const downloadBlob = (blob, filename) => {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(link.href);
                };

                const loadImage = (src) => new Promise((resolve, reject) => {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => resolve(img);
                    img.onerror = () => reject(new Error('Image failed to load'));
                    img.src = src;
                });

                const isJpegSource = (source = '', mimeType = '') => {
                    const normalizedMime = (mimeType || '').toLowerCase();
                    if (normalizedMime.includes('jpeg') || normalizedMime.includes('jpg')) {
                        return true;
                    }
                    const normalizedSource = (source || '').toLowerCase();
                    return normalizedSource.startsWith('data:image/jpeg') || /\.(jpe?g)(?:$|\?)/i.test(normalizedSource);
                };

                const getImageObjectSource = (imageObj) => {
                    if (!imageObj) {
                        return '';
                    }
                    if (typeof imageObj.getSrc === 'function') {
                        try {
                            const src = imageObj.getSrc();
                            if (src) {
                                return src;
                            }
                        } catch (err) {
                            // Ignore and fallback to element source.
                        }
                    }
                    const element = imageObj.getElement?.() || imageObj._element;
                    return element?.src || '';
                };

                const removeBackgroundFromRasterSource = (source) => new Promise((resolve, reject) => {
                    const image = new Image();
                    image.crossOrigin = 'anonymous';
                    image.onload = () => {
                        try {
                            const width = image.naturalWidth || image.width;
                            const height = image.naturalHeight || image.height;
                            if (!width || !height) {
                                reject(new Error('Invalid image dimensions.'));
                                return;
                            }

                            const tempCanvas = document.createElement('canvas');
                            tempCanvas.width = width;
                            tempCanvas.height = height;
                            const ctx = tempCanvas.getContext('2d', { willReadFrequently: true });
                            if (!ctx) {
                                reject(new Error('Canvas context unavailable.'));
                                return;
                            }

                            ctx.drawImage(image, 0, 0, width, height);
                            const imageData = ctx.getImageData(0, 0, width, height);
                            const pixels = imageData.data;
                            const pixelCount = width * height;

                            let sumR = 0;
                            let sumG = 0;
                            let sumB = 0;
                            let sampleCount = 0;

                            const samplePixel = (idx) => {
                                const offset = idx * 4;
                                const alpha = pixels[offset + 3];
                                if (alpha < 12) {
                                    return;
                                }
                                sumR += pixels[offset];
                                sumG += pixels[offset + 1];
                                sumB += pixels[offset + 2];
                                sampleCount += 1;
                            };

                            for (let x = 0; x < width; x += 1) {
                                samplePixel(x);
                                samplePixel((height - 1) * width + x);
                            }
                            for (let y = 1; y < height - 1; y += 1) {
                                samplePixel(y * width);
                                samplePixel(y * width + (width - 1));
                            }

                            if (!sampleCount) {
                                reject(new Error('Unable to sample image background.'));
                                return;
                            }

                            const avgR = sumR / sampleCount;
                            const avgG = sumG / sampleCount;
                            const avgB = sumB / sampleCount;

                            let deviation = 0;
                            const measureDeviation = (idx) => {
                                const offset = idx * 4;
                                const alpha = pixels[offset + 3];
                                if (alpha < 12) {
                                    return;
                                }
                                const dr = pixels[offset] - avgR;
                                const dg = pixels[offset + 1] - avgG;
                                const db = pixels[offset + 2] - avgB;
                                deviation += Math.sqrt((dr * dr) + (dg * dg) + (db * db));
                            };

                            for (let x = 0; x < width; x += 1) {
                                measureDeviation(x);
                                measureDeviation((height - 1) * width + x);
                            }
                            for (let y = 1; y < height - 1; y += 1) {
                                measureDeviation(y * width);
                                measureDeviation(y * width + (width - 1));
                            }

                            const averageDeviation = deviation / sampleCount;
                            const tolerance = Math.max(28, Math.min(96, 24 + averageDeviation * 1.8));
                            const queue = new Int32Array(pixelCount);
                            const visited = new Uint8Array(pixelCount);
                            let head = 0;
                            let tail = 0;

                            const isBackgroundPixel = (idx) => {
                                const offset = idx * 4;
                                const alpha = pixels[offset + 3];
                                if (alpha === 0) {
                                    return true;
                                }
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                const dr = r - avgR;
                                const dg = g - avgG;
                                const db = b - avgB;
                                const distance = Math.sqrt((dr * dr) + (dg * dg) + (db * db));
                                const nearWhite = r > 244 && g > 244 && b > 244;
                                return nearWhite || distance <= tolerance;
                            };

                            const enqueue = (idx) => {
                                if (idx < 0 || idx >= pixelCount || visited[idx]) {
                                    return;
                                }
                                if (!isBackgroundPixel(idx)) {
                                    return;
                                }
                                visited[idx] = 1;
                                queue[tail] = idx;
                                tail += 1;
                            };

                            for (let x = 0; x < width; x += 1) {
                                enqueue(x);
                                enqueue((height - 1) * width + x);
                            }
                            for (let y = 1; y < height - 1; y += 1) {
                                enqueue(y * width);
                                enqueue(y * width + (width - 1));
                            }

                            while (head < tail) {
                                const idx = queue[head];
                                head += 1;
                                const offset = idx * 4;
                                pixels[offset + 3] = 0;

                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                if (x > 0) {
                                    enqueue(idx - 1);
                                }
                                if (x < width - 1) {
                                    enqueue(idx + 1);
                                }
                                if (y > 0) {
                                    enqueue(idx - width);
                                }
                                if (y < height - 1) {
                                    enqueue(idx + width);
                                }
                            }

                            ctx.putImageData(imageData, 0, 0);
                            resolve(tempCanvas.toDataURL('image/png'));
                        } catch (error) {
                            reject(error);
                        }
                    };
                    image.onerror = () => reject(new Error('Could not load image for background removal.'));
                    image.src = source;
                });

                const replaceImageLayerSource = (targetImage, newSource) => new Promise((resolve, reject) => {
                    if (!targetImage) {
                        resolve(null);
                        return;
                    }
                    const previousData = { ...(targetImage.data || {}) };
                    const isLocked = Boolean(previousData.locked);
                    const transform = {
                        left: targetImage.left,
                        top: targetImage.top,
                        originX: targetImage.originX,
                        originY: targetImage.originY,
                        scaleX: targetImage.scaleX,
                        scaleY: targetImage.scaleY,
                        angle: targetImage.angle,
                        flipX: targetImage.flipX,
                        flipY: targetImage.flipY,
                        opacity: targetImage.opacity,
                        skewX: targetImage.skewX || 0,
                        skewY: targetImage.skewY || 0,
                        globalCompositeOperation: targetImage.globalCompositeOperation,
                    };

                    fabric.Image.fromURL(newSource, (nextImage) => {
                        if (!nextImage) {
                            reject(new Error('Unable to apply processed image.'));
                            return;
                        }
                        nextImage.set({
                            ...transform,
                            selectable: !isLocked,
                            evented: !isLocked,
                            hasControls: !isLocked,
                            lockMovementX: isLocked,
                            lockMovementY: isLocked,
                            lockScalingX: isLocked,
                            lockScalingY: isLocked,
                            lockRotation: isLocked,
                            data: {
                                ...previousData,
                                sourceMime: 'image/png',
                                backgroundRemoved: true,
                            },
                        });
                        ensureLayerMetadata(nextImage);
                        nextImage.clipPath = createClipPath();
                        canvas.remove(targetImage);
                        canvas.add(nextImage);
                        canvas.setActiveObject(nextImage);
                        setLayerOrder();
                        canvas.renderAll();
                        saveState();
                        refreshBuilder();
                        resolve(nextImage);
                    }, { crossOrigin: 'anonymous' });
                });

                const setRemoveBgButtonState = (busy) => {
                    if (!removeBgBtn) {
                        return;
                    }
                    removeBgBtn.disabled = busy;
                    removeBgBtn.textContent = busy ? 'Removing...' : 'Remove Background';
                };

                const removeBackgroundFromActiveImage = async () => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design' || active.type !== 'image') {
                        alert('Select a JPEG image layer first, then click Remove Background.');
                        return;
                    }

                    const source = getImageObjectSource(active);
                    const mimeType = active?.data?.sourceMime || inferMimeTypeFromSource(source);
                    if (!isJpegSource(source, mimeType)) {
                        alert('Remove Background currently supports JPEG uploads. Select a JPG/JPEG layer and try again.');
                        return;
                    }

                    setRemoveBgButtonState(true);
                    try {
                        const processedSource = await removeBackgroundFromRasterSource(source);
                        await replaceImageLayerSource(active, processedSource);
                    } catch (error) {
                        alert(error?.message || 'Unable to remove image background right now.');
                    } finally {
                        setRemoveBgButtonState(false);
                    }
                };

                const buildDesignLayerUrl = () => new Promise((resolve) => {
                    const designObjects = canvas.getObjects().filter((obj) => obj?.data?.role === 'design');
                    if (!designObjects.length) {
                        resolve(null);
                        return;
                    }
                    const objectData = designObjects.map((obj) =>
                        obj.toObject(['data', 'clipPath', 'globalCompositeOperation'])
                    );
                    fabric.util.enlivenObjects(objectData, (enlivened) => {
                        const temp = new fabric.StaticCanvas(null, {
                            width: CANVAS_WIDTH,
                            height: CANVAS_HEIGHT,
                            backgroundColor: null,
                        });
                        enlivened.forEach((obj) => {
                            if (!obj?.data) {
                                obj.data = { role: 'design' };
                            }
                            obj.set({
                                selectable: false,
                                evented: false,
                            });
                            obj.clipPath = createClipPath();
                            temp.add(obj);
                        });
                        temp.renderAll();
                        const url = temp.toDataURL({ format: 'png', multiplier: 1 });
                        temp.dispose?.();
                        resolve(url);
                    });
                });

                const exportViewsZip = async () => {
                    const zipApi = window.JSZip;
                    if (!zipApi) {
                        exportCanvas('png');
                        return;
                    }
                    const includeBg = exportBgToggle.checked;
                    const prevGuides = guidesVisible;
                    setGuidesVisible(false);
                    const views = getMockupViews(currentMockup);
                    const colors = getProductColors(currentMockup);
                    const color = colors[currentColorIndex]?.value || '#ffffff';
                    const bgValue = includeBg ? (canvas.backgroundColor || resolveBackground(currentBgTheme)) : null;
                    const designLayerUrl = await buildDesignLayerUrl();
                    const zip = new zipApi();
                    const mockupLabel = mockupConfig[currentMockup]?.label || currentMockup;
                    const safeName = mockupLabel.replace(/\s+/g, '-').toLowerCase();

                    for (const view of views) {
                        const svg = mockupSvgs[currentMockup](color);
                        const fallbackUrl = toDataUrl(svg);
                        const baseUrl = getMockupImageUrl(currentMockup, view, color) || fallbackUrl;
                        const output = document.createElement('canvas');
                        output.width = CANVAS_WIDTH;
                        output.height = CANVAS_HEIGHT;
                        const ctx = output.getContext('2d');
                        if (bgValue) {
                            ctx.fillStyle = bgValue;
                            ctx.fillRect(0, 0, output.width, output.height);
                        }
                        try {
                            const baseImg = await loadImage(baseUrl);
                            const scale = Math.max(output.width / baseImg.width, output.height / baseImg.height);
                            const drawW = baseImg.width * scale;
                            const drawH = baseImg.height * scale;
                            const drawX = (output.width - drawW) / 2;
                            const drawY = (output.height - drawH) / 2;
                            ctx.drawImage(baseImg, drawX, drawY, drawW, drawH);
                        } catch (error) {
                            // Ignore base image failures, still export design layer if available.
                        }

                        if (designLayerUrl) {
                            try {
                                const designImg = await loadImage(designLayerUrl);
                                ctx.drawImage(designImg, 0, 0, output.width, output.height);
                            } catch (error) {
                                // Ignore design layer failures.
                            }
                        }

                        const dataUrl = output.toDataURL('image/png');
                        const base64 = dataUrl.split(',')[1];
                        zip.file(`${safeName}-${view}.png`, base64, { base64: true });
                    }

                    const blob = await zip.generateAsync({ type: 'blob' });
                    downloadBlob(blob, `${safeName}-mockups.zip`);
                    if (prevGuides) {
                        setGuidesVisible(true);
                    }
                };

                const updateOpacitySlider = () => {
                    const active = canvas.getActiveObject();
                    if (!active || active?.data?.role !== 'design') {
                        opacityRange.value = 100;
                        if (builderOpacityInput) {
                            builderOpacityInput.value = 100;
                        }
                        if (builderOpacityValue) {
                            builderOpacityValue.textContent = '100%';
                        }
                        return;
                    }
                    const value = Math.round((active.opacity ?? 1) * 100);
                    opacityRange.value = value;
                    if (builderOpacityInput) {
                        builderOpacityInput.value = value;
                    }
                    if (builderOpacityValue) {
                        builderOpacityValue.textContent = `${value}%`;
                    }
                };

                uploadDrops.forEach((drop) => {
                    drop.addEventListener('click', () => uploadInput.click());
                });
                if (uploadBtn) {
                    uploadBtn.addEventListener('click', () => uploadInput.click());
                }

                uploadInput.addEventListener('change', (event) => {
                    const files = Array.from(event.target.files || []);
                    files.forEach(addUpload);
                    uploadInput.value = '';
                });

                uploadDrops.forEach((drop) => {
                    drop.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        drop.classList.add('is-active');
                    });
                    drop.addEventListener('dragleave', () => drop.classList.remove('is-active'));
                    drop.addEventListener('drop', (event) => {
                        event.preventDefault();
                        drop.classList.remove('is-active');
                        const files = Array.from(event.dataTransfer?.files || []);
                        files.forEach(addUpload);
                    });
                });

                document.querySelectorAll('[data-mockup]').forEach((button) => {
                    button.addEventListener('click', () => {
                        document.querySelectorAll('[data-mockup]').forEach((el) => el.classList.remove('is-active'));
                        button.classList.add('is-active');
                        loadMockup(button.dataset.mockup, null);
                        renderColors();
                    });
                });

                document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
                    const track = carousel.querySelector('[data-product-track]');
                    const prev = carousel.querySelector('[data-product-prev]');
                    const next = carousel.querySelector('[data-product-next]');
                    if (!track) {
                        return;
                    }
                    const step = () => {
                        const card = track.querySelector('.mockup-app-tile');
                        if (!card) {
                            return 0;
                        }
                        const gap = parseFloat(getComputedStyle(track).gap || '0');
                        return card.getBoundingClientRect().width + gap;
                    };

                    prev?.addEventListener('click', () => {
                        track.scrollBy({ left: -step(), behavior: 'smooth' });
                    });
                    next?.addEventListener('click', () => {
                        track.scrollBy({ left: step(), behavior: 'smooth' });
                    });
                });

                document.querySelectorAll('[data-align]').forEach((button) => {
                    button.addEventListener('click', () => alignObject(button.dataset.align));
                });

                document.querySelectorAll('[data-layer]').forEach((button) => {
                    button.addEventListener('click', () => moveLayer(button.dataset.layer));
                });

                document.querySelectorAll('[data-flip]').forEach((button) => {
                    button.addEventListener('click', () => flipActive(button.dataset.flip));
                });

                document.querySelectorAll('[data-export]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const type = button.dataset.export;
                        if (type === 'zip') {
                            exportViewsZip();
                            return;
                        }
                        exportCanvas(type === 'jpg' ? 'jpeg' : 'png');
                    });
                });

                const templateInput = document.querySelector('[data-template-input]');
                const templateSubmit = document.querySelector('[data-template-submit]');
                const templateGenerate = document.querySelector('[data-template-generate]');
                const templateTabs = document.querySelectorAll('[data-template-tab]');
                const templateUpload = document.querySelector('[data-template-upload]');
                const templateUploadInput = document.querySelector('[data-template-upload-input]');
                const templateGrid = document.querySelector('[data-template-grid]');
                const templateVoice = document.querySelector('[data-template-voice]');
                const templateUploads = [];
                const templateStaticItems = document.querySelectorAll('[data-template-static]');
                const templateGenerateLabel = templateGenerate?.textContent?.trim() || 'Generate design';
                let templateIsGenerating = false;

                const getActiveTemplateTab = () => {
                    const active = document.querySelector('[data-template-tab].is-active');
                    return active?.dataset?.templateTab || 'templates';
                };

                const updateTemplateSubmit = () => {
                    if (!templateSubmit || !templateInput) {
                        return;
                    }
                    const hasValue = templateInput.value.trim().length > 0;
                    templateSubmit.disabled = templateIsGenerating || !hasValue;
                    if (templateGenerate) {
                        templateGenerate.disabled = templateIsGenerating || !hasValue;
                    }
                };

                const setTemplateGenerating = (isGenerating) => {
                    templateIsGenerating = isGenerating;
                    if (templateGenerate) {
                        templateGenerate.textContent = isGenerating ? 'Generating…' : templateGenerateLabel;
                        templateGenerate.setAttribute('aria-busy', isGenerating ? 'true' : 'false');
                    }
                    if (templateSubmit) {
                        templateSubmit.setAttribute('aria-busy', isGenerating ? 'true' : 'false');
                    }
                    updateTemplateSubmit();
                };

                templateInput?.addEventListener('input', updateTemplateSubmit);
                updateTemplateSubmit();

                templateTabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        templateTabs.forEach((btn) => btn.classList.remove('is-active'));
                        tab.classList.add('is-active');
                        updateTemplateSubmit();
                    });
                });

                const removeRenderedTemplateUploads = () => {
                    if (!templateGrid) {
                        return;
                    }
                    templateGrid.querySelectorAll('[data-template-uploaded]').forEach((node) => node.remove());
                };

                const renderTemplateUploads = () => {
                    if (!templateGrid) {
                        return;
                    }
                    removeRenderedTemplateUploads();
                    const placeholders = templateGrid.querySelectorAll('[data-template-placeholder]');
                    placeholders.forEach((placeholder) => {
                        placeholder.hidden = templateUploads.length > 0;
                    });
                    templateUploads.forEach((upload) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'template-ai-grid-item is-upload';
                        btn.dataset.templateUploaded = 'true';
                        btn.title = upload.name;
                        const img = document.createElement('img');
                        img.src = upload.url;
                        img.alt = upload.name;
                        btn.appendChild(img);
                        btn.addEventListener('click', () => placeUpload(upload.url));
                        templateGrid.appendChild(btn);
                    });
                };

                const addTemplateUpload = (file) => {
                    if (!file) {
                        return;
                    }
                    const isAllowedType = ['image/png', 'image/jpeg', 'image/svg+xml'].includes(file.type);
                    const hasAllowedExt = /\.(png|jpe?g|svg)$/i.test(file.name || '');
                    if (!isAllowedType && !hasAllowedExt) {
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const dataUrl = event.target?.result;
                        if (!dataUrl) {
                            return;
                        }
                        templateUploads.unshift({
                            id: `${Date.now()}-${Math.random()}`,
                            name: file.name,
                            url: dataUrl,
                            file,
                        });
                        renderTemplateUploads();
                    };
                    reader.readAsDataURL(file);
                };

                const generateTemplateDesign = async () => {
                    if (!templateInput || templateIsGenerating) {
                        return;
                    }

                    const prompt = templateInput.value.trim();
                    if (!prompt) {
                        updateTemplateSubmit();
                        return;
                    }

                    setTemplateGenerating(true);

                    try {
                        const formData = new FormData();
                        formData.append('prompt', prompt);
                        formData.append('style_tab', getActiveTemplateTab());

                        const response = await fetch(templateGenerateUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: formData,
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(payload?.message || 'Unable to generate design right now.');
                        }

                        const imageDataUrl = payload?.image_data_url || payload?.image_url || '';
                        if (!imageDataUrl) {
                            throw new Error('AI returned no image. Try a different prompt.');
                        }

                        const generatedName = `AI Design ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                        templateUploads.unshift({
                            id: `ai-${Date.now()}-${Math.random()}`,
                            name: generatedName,
                            url: imageDataUrl,
                            file: null,
                        });
                        renderTemplateUploads();
                        placeUpload(imageDataUrl);
                    } catch (error) {
                        alert(error?.message || 'Unable to generate design right now.');
                    } finally {
                        setTemplateGenerating(false);
                    }
                };

                templateSubmit?.addEventListener('click', generateTemplateDesign);

                templateGenerate?.addEventListener('click', generateTemplateDesign);

                templateInput?.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        generateTemplateDesign();
                    }
                });

                templateUpload?.addEventListener('click', () => {
                    templateUploadInput?.click();
                });

                templateUploadInput?.addEventListener('change', (event) => {
                    const files = Array.from(event.target.files || []);
                    files.forEach((file) => addTemplateUpload(file));
                    templateUploadInput.value = '';
                });

                templateStaticItems.forEach((item) => {
                    const url = item.dataset.templateUrl;
                    if (!url) {
                        return;
                    }
                    item.addEventListener('click', () => placeUpload(url));
                });

                templateVoice?.addEventListener('click', () => {
                    alert('Voice input coming soon.');
                });


                const clampTextSize = (value) => Math.min(300, Math.max(6, value));

                const applyTextSize = (value) => {
                    const size = clampTextSize(value);
                    if (textSizeInput) {
                        textSizeInput.value = String(size);
                    }
                    applyTextStyles({ fontSize: size });
                };

                const applyCaseTransform = (text, mode) => {
                    if (!text) {
                        return text;
                    }
                    switch (mode) {
                        case 'upper':
                            return text.toUpperCase();
                        case 'lower':
                            return text.toLowerCase();
                        case 'title':
                            return text.replace(/\w\S*/g, (word) =>
                                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                            );
                        default:
                            return text;
                    }
                };

                textColorInput?.addEventListener('input', (event) => {
                    const value = event.target.value;
                    applyTextStyles({ fill: value });
                });

                textFontSelect?.addEventListener('change', (event) => {
                    const value = event.target.value;
                    applyTextStyles({ fontFamily: value });
                });

                textSizeInput?.addEventListener('input', (event) => {
                    const value = Number.parseInt(event.target.value || '0', 10);
                    if (!Number.isNaN(value)) {
                        applyTextSize(value);
                    }
                });

                textSizeDecBtn?.addEventListener('click', () => {
                    const current = Number.parseInt(textSizeInput?.value || '36', 10);
                    applyTextSize(current - 1);
                });

                textSizeIncBtn?.addEventListener('click', () => {
                    const current = Number.parseInt(textSizeInput?.value || '36', 10);
                    applyTextSize(current + 1);
                });

                textBoldBtn?.addEventListener('click', () => {
                    const target = getActiveTextTarget();
                    if (!target) {
                        return;
                    }
                    const isBold = String(target.fontWeight || '').toLowerCase() === 'bold' || Number(target.fontWeight) >= 600;
                    applyTextStyles({ fontWeight: isBold ? 'normal' : '700' });
                });

                textItalicBtn?.addEventListener('click', () => {
                    const target = getActiveTextTarget();
                    if (!target) {
                        return;
                    }
                    const isItalic = String(target.fontStyle || '').toLowerCase() === 'italic';
                    applyTextStyles({ fontStyle: isItalic ? 'normal' : 'italic' });
                });

                textUnderlineBtn?.addEventListener('click', () => {
                    const target = getActiveTextTarget();
                    if (!target) {
                        return;
                    }
                    const isUnderline = Boolean(target.underline);
                    applyTextStyles({ underline: !isUnderline });
                });

                textStrikeBtn?.addEventListener('click', () => {
                    const target = getActiveTextTarget();
                    if (!target) {
                        return;
                    }
                    const isStrike = Boolean(target.linethrough);
                    applyTextStyles({ linethrough: !isStrike });
                });

                textCaseBtn?.addEventListener('click', () => {
                    const target = getActiveTextTarget();
                    if (!target) {
                        return;
                    }
                    const currentMode = textCaseBtn.dataset.caseMode || 'none';
                    const nextMode = currentMode === 'none'
                        ? 'upper'
                        : currentMode === 'upper'
                            ? 'lower'
                            : currentMode === 'lower'
                                ? 'title'
                                : 'none';
                    textCaseBtn.dataset.caseMode = nextMode;
                    const newText = applyCaseTransform(target.text, nextMode);
                    applyTextStyles({ text: newText });
                });

                textAlignBtns.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const align = btn.dataset.textAlign || 'left';
                        applyTextStyles({ textAlign: align });
                        textAlignBtns.forEach((item) =>
                            item.classList.toggle('is-active', item === btn)
                        );
                    });
                });

                textSpacingBtn?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (textSpacingPanel) {
                        textSpacingPanel.hidden = !textSpacingPanel.hidden;
                    }
                });

                textLineHeightInput?.addEventListener('input', (event) => {
                    const value = Number.parseFloat(event.target.value || '1');
                    if (!Number.isNaN(value)) {
                        applyTextStyles({ lineHeight: value });
                    }
                });

                textLetterSpacingInput?.addEventListener('input', (event) => {
                    const value = Number.parseInt(event.target.value || '0', 10);
                    if (!Number.isNaN(value)) {
                        applyTextStyles({ charSpacing: value });
                    }
                });

                textEffectsBtn?.addEventListener('click', () => {
                    alert('Text effects coming soon.');
                });

                textAnimateBtn?.addEventListener('click', () => {
                    alert('Text animations coming soon.');
                });

                textPositionBtn?.addEventListener('click', () => {
                    setActivePanel('edit');
                });

                textPaintBtn?.addEventListener('click', () => {
                    alert('More styling tools coming soon.');
                });

                removeBgBtn?.addEventListener('click', () => {
                    removeBackgroundFromActiveImage();
                });

                toolButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const tool = button.dataset.tool;
                        setActiveTool(tool);
                        if (tool === 'text') {
                            addTextTool();
                        } else if (tool === 'table') {
                            addTableTool();
                        } else if (tool === 'sticky') {
                            addStickyTool();
                        } else if (tool === 'line') {
                            addLineTool();
                        } else if (tool === 'shape') {
                            addShapeTool();
                        }
                    });
                });

                mockupTabs.forEach((button) => {
                    button.addEventListener('click', () => {
                        const tab = button.dataset.mockupTab;
                        if (tab) {
                            setMockupTab(tab);
                        }
                    });
                });

                railButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        setActivePanel(button.dataset.panel);
                    });
                });

                viewNavButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const dir = button.dataset.viewNav === 'next' ? 1 : -1;
                        navigateView(dir);
                    });
                });

                panelTargets.forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = button.dataset.panelTarget;
                        if (target) {
                            setActivePanel(target);
                        }
                    });
                });

                if (fileToggle) {
                    fileToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        toggleFileMenu();
                    });
                }

                if (shareToggle && shareWrap) {
                    shareToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        shareWrap.classList.toggle('is-open');
                    });
                }

                if (toolsToggle && toolsMenu) {
                    toolsToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        toggleToolsMenu();
                    });
                }

                const toggleMobileDrawer = (force = null) => {
                    if (!mobileDrawer) {
                        return;
                    }
                    const open = force ?? !mobileDrawer.classList.contains('is-open');
                    mobileDrawer.classList.toggle('is-open', open);
                    mobileDrawer.hidden = !open;

                    const moveNode = (node) => {
                        if (!node || mobileDrawer.contains(node)) {
                            return;
                        }
                        const marker = document.createComment('mobile-placeholder');
                        node.parentNode?.insertBefore(marker, node);
                        node.__mobilePlaceholder = marker;
                        mobileDrawer.appendChild(node);
                    };

                    const restoreNode = (node) => {
                        const marker = node?.__mobilePlaceholder;
                        if (!marker || !marker.parentNode) {
                            return;
                        }
                        marker.parentNode.insertBefore(node, marker);
                        marker.remove();
                        node.__mobilePlaceholder = null;
                    };

                    if (open) {
                        moveNode(topbarGroup);
                        moveNode(topbarRight);
                    } else {
                        restoreNode(topbarGroup);
                        restoreNode(topbarRight);
                    }
                };

                if (mobileToggle) {
                    mobileToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        toggleMobileDrawer();
                    });
                }

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 900) {
                        toggleMobileDrawer(false);
                    }
                });

                if (brandUploadInput) {
                    brandUploadInput.addEventListener('change', (event) => {
                        const file = event.target.files?.[0];
                        if (!file) {
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = () => {
                            const result = typeof reader.result === 'string' ? reader.result : '';
                            if (result) {
                                localStorage.setItem(BRAND_LOGO_KEY, result);
                                applyBrandLogo(result);
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                }

                if (brandClearBtn) {
                    brandClearBtn.addEventListener('click', () => {
                        localStorage.removeItem(BRAND_LOGO_KEY);
                        applyBrandLogo(brandLogoImg?.dataset.defaultLogo || '');
                    });
                }

                document.querySelectorAll('[data-file-action]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.dataset.fileAction;
                        switch (action) {
                            case 'new':
                                clearDesignObjects();
                                break;
                            case 'upload':
                                uploadInput?.click();
                                break;
                            case 'settings':
                                cycleBackgroundTheme();
                                break;
                            case 'accessibility':
                                setGuidesVisible(!guidesVisible);
                                break;
                            case 'save':
                                saveDesign();
                                break;
                            case 'move':
                                setActivePanel('mockups');
                                setMockupTab('designs');
                                break;
                            case 'copy':
                                saveDesignCopy();
                                break;
                            case 'download':
                                exportCanvas('png');
                                break;
                            case 'history':
                                setActivePanel('mockups');
                                setMockupTab('designs');
                                break;
                            case 'trash':
                                clearDesignObjects();
                                break;
                            default:
                                break;
                        }
                        hideFileMenu();
                    });
                });

                contextItems.forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.dataset.action;
                        const active = canvas.getActiveObject();
                        const requiresSelection = [
                            'copy',
                            'cut',
                            'duplicate',
                            'delete',
                            'align-left',
                            'align-center',
                            'align-right',
                            'align-top',
                            'align-middle',
                            'align-bottom',
                            'send-back',
                            'send-backward',
                            'bring-forward',
                            'bring-front',
                            'comment',
                            'lock',
                            'timing',
                        ];
                        if (!active && requiresSelection.includes(action)) {
                            return;
                        }
                        switch (action) {
                            case 'copy':
                                copyActive();
                                break;
                            case 'cut':
                                copyActive();
                                deleteActive();
                                break;
                            case 'paste':
                                pasteActive();
                                break;
                            case 'duplicate':
                                copyActive();
                                pasteActive();
                                break;
                            case 'delete':
                                deleteActive();
                                break;
                            case 'align-left':
                                alignActive('left');
                                break;
                            case 'align-center':
                                alignActive('center');
                                break;
                            case 'align-right':
                                alignActive('right');
                                break;
                            case 'align-top':
                                alignActive('top');
                                break;
                            case 'align-middle':
                                alignActive('middle');
                                break;
                            case 'align-bottom':
                                alignActive('bottom');
                                break;
                            case 'send-back':
                                moveLayerToEdge('back');
                                break;
                            case 'send-backward':
                                moveLayer('backward');
                                break;
                            case 'bring-forward':
                                moveLayer('forward');
                                break;
                            case 'bring-front':
                                moveLayerToEdge('front');
                                break;
                            case 'comment':
                                alert('Comment tool coming soon.');
                                break;
                            case 'lock':
                                if (active) {
                                    updateLockedState(active);
                                }
                                break;
                            case 'timing':
                                alert('Element timing coming soon.');
                                break;
                            case 'apply-colors':
                                applyColorsToPage();
                                break;
                            case 'download':
                                downloadSelection();
                                break;
                            default:
                                break;
                        }
                        hideContextMenu();
                    });
                });

                saveDesignBtns.forEach((button) => {
                    button.addEventListener('click', saveDesign);
                });

                canvas.upperCanvasEl.addEventListener('contextmenu', (event) => {
                    event.preventDefault();
                });

                canvas.on('mouse:down', (opt) => {
                    const button = opt.e?.button;
                    if (button === 2) {
                        const target = opt.target;
                        const active = canvas.getActiveObject();
                        const selection =
                            target?.data?.role === 'design'
                                ? target
                                : active?.data?.role === 'design'
                                    ? active
                                    : null;
                        if (selection) {
                            canvas.setActiveObject(selection);
                        }
                        const x = opt.e.clientX;
                        const y = opt.e.clientY;
                        showContextMenu(x, y, selection);
                        return;
                    }

                    if (!opt.target && button === 0) {
                        canvas.discardActiveObject();
                        canvas.renderAll();
                    }
                });

                if (canvasWrap && artboard) {
                    canvasWrap.addEventListener('mousedown', (event) => {
                        if (event.button !== 0) {
                            return;
                        }
                        if (!artboard.contains(event.target)) {
                            canvas.discardActiveObject();
                            canvas.renderAll();
                        }
                    });
                }

                window.addEventListener('click', (event) => {
                    if (!contextMenu?.contains(event.target)) {
                        hideContextMenu();
                    }
                    if (!fileMenu?.contains(event.target) && event.target !== fileToggle) {
                        hideFileMenu();
                    }
                    if (shareWrap && !shareWrap.contains(event.target)) {
                        shareWrap.classList.remove('is-open');
                    }
                    if (toolsMenu && !toolsMenu.contains(event.target) && !toolsToggle?.contains(event.target)) {
                        setToolsMenuOpen(false);
                    }
                    if (
                        textSpacingPanel &&
                        !textSpacingPanel.contains(event.target) &&
                        event.target !== textSpacingBtn
                    ) {
                        textSpacingPanel.hidden = true;
                    }
                    if (
                        mobileDrawer?.classList.contains('is-open') &&
                        !mobileDrawer.contains(event.target) &&
                        event.target !== mobileToggle
                    ) {
                        toggleMobileDrawer(false);
                    }
                });

                document.querySelector('[data-delete]').addEventListener('click', deleteActive);
                document.querySelector('[data-duplicate]')?.addEventListener('click', () => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }
                    copyActive();
                    pasteActive();
                });
                document.querySelector('[data-lock]')?.addEventListener('click', () => {
                    const active = canvas.getActiveObject();
                    if (!active) {
                        return;
                    }
                    updateLockedState(active);
                });
                document.querySelector('[data-add]')?.addEventListener('click', () => {
                    setActivePanel('uploads');
                    setMockupTab('designs');
                    uploadInput?.click();
                });
                undoBtn.addEventListener('click', undo);
                redoBtn.addEventListener('click', redo);
                zoomRange.addEventListener('input', () => setZoom(Number(zoomRange.value)));
                zoomRange.value = 100;
                zoomRange.disabled = false;
                opacityRange.addEventListener('input', (event) => updateOpacity(Number(event.target.value)));

                canvas.on('selection:created', updateOpacitySlider);
                canvas.on('selection:updated', updateOpacitySlider);
                canvas.on('selection:cleared', () => {
                    updateOpacitySlider();
                    if (printAreaRect) {
                        printAreaRect.set({ selectable: true, evented: true, hasControls: true });
                    }
                    canvas.renderAll();
                });
                canvas.on('object:modified', saveState);
                canvas.on('text:changed', saveState);
                canvas.on('text:editing:exited', saveState);
                canvas.on('object:added', () => {
                    if (!isRestoring) {
                        updateOpacitySlider();
                    }
                });
                canvas.on('selection:created', (event) => {
                    if (event?.selected?.some((obj) => obj?.data?.role === 'design')) {
                        if (printAreaRect) {
                            printAreaRect.set({ selectable: false, evented: false, hasControls: false });
                        }
                    }
                });
                canvas.on('selection:updated', (event) => {
                    if (event?.selected?.some((obj) => obj?.data?.role === 'design')) {
                        if (printAreaRect) {
                            printAreaRect.set({ selectable: false, evented: false, hasControls: false });
                        }
                    }
                });

                window.addEventListener('keydown', (event) => {
                    const isInput =
                        event.target instanceof HTMLInputElement ||
                        event.target instanceof HTMLTextAreaElement ||
                        event.target?.isContentEditable;
                    if (event.key === 'Escape') {
                        setToolsMenuOpen(false);
                    }
                    if ((event.key === 'Delete' || event.key === 'Backspace') && canvas.getActiveObject() && !isInput) {
                        deleteActive();
                    }
                    if ((event.ctrlKey || event.metaKey) && !event.shiftKey && event.key.toLowerCase() === 'z') {
                        event.preventDefault();
                        undo();
                    }
                    if (
                        (event.ctrlKey || event.metaKey) &&
                        (event.key.toLowerCase() === 'y' || (event.shiftKey && event.key.toLowerCase() === 'z'))
                    ) {
                        event.preventDefault();
                        redo();
                    }
                });

                window.addEventListener('resize', () => {
                    fitToContainer();
                });

                setBackgroundTheme('light');
                loadBrandLogo();
                loadMockup(currentMockup, null);
                renderColors();
                updateHistoryButtons();
                setActiveTool('select');
                setActivePanel('mockups');
                renderSavedDesigns();
            })();
        </script>
    </body>
</html>
