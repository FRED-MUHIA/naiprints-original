<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Nai Prints | Design Studio</title>
        <meta name="description" content="Design custom products with live mockups and print-ready exports.">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600&family=Playfair+Display:wght@600&family=Pacifico&display=swap">
        <script src="https://unpkg.com/vue@3.4.38/dist/vue.global.prod.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900">
        @php
            $header = $globalHeader ?? [];
            $logoUrl = $header['logo_url'] ?? null;
            $pageSections = $pageContent['sections'] ?? [];
            $designerSection = collect($pageSections)->firstWhere('type', 'designer_page') ?? [];
            $designerData = $designerSection['data'] ?? [];
            $brandName = $designerData['brand_name'] ?? 'Nai Prints';
            $brandSubtitle = $designerData['brand_subtitle'] ?? 'Design Studio';
            $topbarTitle = $designerData['topbar_title'] ?? 'Untitled design · 1080px × 1080px';
            $topMenu = $designerData['top_menu'] ?? ['File', 'Resize', 'Upload'];
            $leftRail = $designerData['left_rail'] ?? [
                ['label' => 'Templates', 'icon' => '🧩'],
                ['label' => 'Tools', 'icon' => '🧰'],
                ['label' => 'Projects', 'icon' => '📁'],
                ['label' => 'Uploads', 'icon' => '🧩'],
                ['label' => 'Magic', 'icon' => '✨'],
                ['label' => 'QR', 'icon' => '🔳'],
                ['label' => 'Voice', 'icon' => '🔊'],
                ['label' => 'Tracer', 'icon' => '🧪'],
                ['label' => 'Mockups', 'icon' => '🖼️'],
            ];
            $userInitials = $designerData['user_initials'] ?? 'FM';
            $addToCartLabel = $designerData['add_to_cart_label'] ?? 'Add to cart';
            $addToCartUrl = $designerData['add_to_cart_url'] ?? '#';
            $backToShopLabel = $designerData['back_to_shop_label'] ?? 'Back to shop';
            $backToShopUrl = $designerData['back_to_shop_url'] ?? '/';
            $shareLabel = $designerData['share_label'] ?? 'Share';
            $designerConfig = [
                'mockups' => $designerData['mockups'] ?? [],
                'colors' => $designerData['product_colors'] ?? [],
                'products' => $designerData['products'] ?? [],
                'print_areas' => $designerData['print_areas'] ?? [],
            ];
        @endphp
        <div id="designer-app" class="min-h-screen editor-shell">
            <header class="canva-topbar">
                <div class="canva-topbar-left">
                    <a class="canva-brand" href="/" aria-label="Home">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Nai Prints" class="canva-logo-img">
                        @else
                            <span class="canva-logo">NA</span>
                        @endif
                        <div class="canva-brand-text">
                            <span class="canva-brand-name">{{ $brandName }}</span>
                            <span class="canva-brand-sub">{{ $brandSubtitle }}</span>
                        </div>
                    </a>
                    <nav class="canva-menu">
                        @foreach ($topMenu as $item)
                            @php
                                $menuLabel = $item;
                                $menuKey = strtolower($menuLabel);
                                $isUploadItem = in_array($menuKey, ['upload', 'editing'], true);
                            @endphp
                            @if ($menuKey === 'file')
                                <div class="canva-menu-dropdown" @click.stop>
                                    <button type="button"
                                            class="canva-menu-item"
                                            :class="{ 'is-open': openMenu === 'file' }"
                                            @click="toggleMenu('file')">
                                        {{ $menuLabel }}
                                    </button>
                                    <div v-if="openMenu === 'file'" class="canva-menu-panel">
                                        <button type="button" class="canva-menu-option" @click="downloadPng">Download PNG</button>
                                        <button type="button" class="canva-menu-option" @click="downloadJpg">Download JPEG</button>
                                        <button type="button" class="canva-menu-option" @click="downloadPdf">Download PDF</button>
                                        <button type="button" class="canva-menu-option" @click="downloadSvg">Download Vector (SVG)</button>
                                        <button type="button" class="canva-menu-option" @click="downloadAllViewsZip">Download All Views (ZIP)</button>
                                    </div>
                                </div>
                            @elseif ($isUploadItem)
                                <button type="button" class="canva-menu-item" @click="triggerMenuUpload">Upload</button>
                            @else
                                <button type="button" class="canva-menu-item">{{ $menuLabel }}</button>
                            @endif
                        @endforeach
                    </nav>
                    <input type="file"
                           class="sr-only"
                           ref="menuUploadRef"
                           accept="image/*,.svg"
                           @change="handleUpload">
                    <div class="canva-history">
                        <button class="canva-icon-btn" type="button" @click="undo" :disabled="!canUndo">↺</button>
                        <button class="canva-icon-btn" type="button" @click="redo" :disabled="!canRedo">↻</button>
                    </div>
                </div>
                <div class="canva-topbar-center">
                    {{ $topbarTitle }}
                </div>
                <div class="canva-topbar-right">
                    <button class="canva-avatar">{{ $userInitials }}</button>
                    <button type="button" class="canva-pill" @click="saveProjectSnapshot">Save</button>
                    <button class="canva-icon-btn">📊</button>
                    <button class="canva-icon-btn">💬</button>
                    <a class="canva-action-primary" href="{{ $addToCartUrl }}">{{ $addToCartLabel }}</a>
                    <a class="canva-action-secondary" href="{{ $backToShopUrl }}">{{ $backToShopLabel }}</a>
                    <button class="canva-share">{{ $shareLabel }}</button>
                </div>
            </header>

            <div class="canva-layout">
                <aside class="canva-rail">
                    <button class="canva-rail-item"
                            :class="{ 'is-active': activeRail === 'Products' }"
                            @click="selectRail('Products')">
                        <span>🛍️</span>
                        <span>Products</span>
                    </button>
                    @foreach ($leftRail as $index => $railItem)
                        @php
                            $railLabel = $railItem['label'] ?? '';
                            if ($railLabel === 'Apps') {
                                $railLabel = 'Uploads';
                            }
                        @endphp
                        <button class="canva-rail-item"
                                :class="{ 'is-active': activeRail === '{{ $railLabel }}' }"
                                @click="selectRail('{{ $railLabel }}')">
                            <span>{{ $railItem['icon'] ?? '' }}</span>
                            <span>{{ $railLabel }}</span>
                        </button>
                    @endforeach
                </aside>

                <aside class="canva-library">
                    <div class="canva-library-scroll">
                        <div v-if="activeRail === 'Projects'" class="canva-section">
                            <h3>Project History</h3>
                            <div class="editor-history-list">
                                <div v-if="!projectHistory.length" class="editor-history-empty">
                                    No saved history yet.
                                </div>
                                <div v-for="entry in projectHistory" :key="entry.id" class="editor-history-item">
                                    <div class="editor-history-meta">
                                        <div class="editor-history-title">
                                            @{{ entry.productName || 'Design' }} · @{{ entry.viewLabel || 'View' }}
                                        </div>
                                        <div class="editor-history-sub">
                                            @{{ formatHistoryTime(entry.timestamp) }} · @{{ formatHistoryExpiry(entry.timestamp) }}
                                        </div>
                                    </div>
                                    <div class="editor-history-actions">
                                        <button type="button" class="editor-history-btn" @click="loadHistoryEntry(entry.id)">Load</button>
                                        <button type="button" class="editor-history-btn ghost" @click="deleteHistoryEntry(entry.id)">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="activeRail === 'Tools'">
                            <div class="canva-section">
                                <h3>Tools</h3>
                                <div class="editor-tools-grid">
                                    <button v-for="tool in toolItems"
                                            :key="tool.id"
                                            type="button"
                                            class="editor-tool-btn"
                                            :class="{ 'is-active': tool.id === selectedToolId }"
                                            @click="selectTool(tool.id)">
                                        <span class="editor-tool-icon">@{{ tool.icon }}</span>
                                        <span class="editor-tool-label">@{{ tool.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="canva-section">
                                <h3>Attributes</h3>

                                <div v-if="selectedToolId === 'upload'" class="editor-tool-attrs">
                                    <label class="editor-attr-label">Upload Image</label>
                                    <input type="file" class="editor-input" accept="image/*,.svg" @change="handleUpload">
                                </div>

                                <div v-else-if="selectedToolId === 'text'" class="editor-tool-attrs">
                                    <label class="editor-attr-label">Text</label>
                                    <input type="text" class="editor-input" v-model="textValue" placeholder="Type your text">
                                    <label class="editor-attr-label">Font</label>
                                    <select class="editor-input" v-model="selectedFont">
                                        <option v-for="font in fonts" :key="font" :value="font">@{{ font }}</option>
                                    </select>
                                    <label class="editor-attr-label">Size</label>
                                    <input type="range" min="8" max="120" v-model.number="textSize" class="editor-range">
                                    <label class="editor-attr-label">Color</label>
                                    <input type="color" v-model="textColor" class="editor-color-input">
                                    <button type="button" class="editor-btn w-full" @click="addText">Add Text</button>
                                </div>

                                <div v-else-if="selectedToolId === 'rect'" class="editor-tool-attrs">
                                    <label class="editor-attr-label">Fill</label>
                                    <input type="color" v-model="shapeFill" class="editor-color-input">
                                    <label class="editor-attr-label">Stroke</label>
                                    <input type="color" v-model="shapeStroke" class="editor-color-input">
                                    <label class="editor-attr-label">Stroke Width</label>
                                    <input type="range" min="0" max="20" v-model.number="shapeStrokeWidth" class="editor-range">
                                    <button type="button" class="editor-btn w-full" @click="addRectangle">Add Rectangle</button>
                                </div>

                                <div v-else-if="selectedToolId === 'circle'" class="editor-tool-attrs">
                                    <label class="editor-attr-label">Fill</label>
                                    <input type="color" v-model="shapeFill" class="editor-color-input">
                                    <label class="editor-attr-label">Stroke</label>
                                    <input type="color" v-model="shapeStroke" class="editor-color-input">
                                    <label class="editor-attr-label">Stroke Width</label>
                                    <input type="range" min="0" max="20" v-model.number="shapeStrokeWidth" class="editor-range">
                                    <button type="button" class="editor-btn w-full" @click="addCircle">Add Circle</button>
                                </div>

                                <div v-else-if="selectedToolId === 'line'" class="editor-tool-attrs">
                                    <label class="editor-attr-label">Stroke</label>
                                    <input type="color" v-model="lineStroke" class="editor-color-input">
                                    <label class="editor-attr-label">Stroke Width</label>
                                    <input type="range" min="1" max="20" v-model.number="lineStrokeWidth" class="editor-range">
                                    <button type="button" class="editor-btn w-full" @click="addLine">Add Line</button>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="activeRail === 'Templates'" class="canva-section">
                            <div class="template-hero">
                                <input type="text" class="template-input" placeholder="Describe your ideal design">
                                <div class="template-hero-actions">
                                    <button type="button" class="template-hero-btn">🖼️</button>
                                    <button type="button" class="template-hero-btn">🎤</button>
                                    <button type="button" class="template-hero-btn">➜</button>
                                </div>
                            </div>
                            <div class="template-tabs">
                                <button type="button" class="template-tab is-active">Templates</button>
                                <button type="button" class="template-tab">Styles</button>
                            </div>
                            <div class="template-section">
                                <h3>Recently used</h3>
                                <div class="template-grid">
                                    <div class="template-card template-card-donut">
                                        <span>Donuts</span>
                                    </div>
                                    <div class="template-card template-card-burger">
                                        <span>Special Burger</span>
                                    </div>
                                </div>
                            </div>
                            <div class="template-section">
                                <h3>All results</h3>
                                <div class="template-grid template-grid-wide">
                                    <div class="template-card template-card-photo">Editorial</div>
                                    <div class="template-card template-card-photo alt">Street</div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="activeRail === 'Uploads'" class="canva-section">
                            <div class="editor-upload-header">
                                <h3>Uploads</h3>
                                <button type="button" class="editor-upload-btn" @click="triggerMenuUpload">Upload</button>
                            </div>
                            <div class="editor-upload-grid">
                                <div v-if="!uploads.length" class="editor-history-empty">
                                    No uploads yet.
                                </div>
                                <button v-for="item in uploads"
                                        :key="item.id"
                                        type="button"
                                        class="editor-upload-item"
                                        draggable="true"
                                        @dragstart="onUploadDragStart(item, $event)"
                                        @click="placeUpload(item)">
                                    <img :src="item.url" alt="" class="editor-upload-thumb">
                                    <div class="editor-upload-meta">
                                        <div class="editor-upload-name">@{{ item.name || 'Upload' }}</div>
                                        <div class="editor-upload-sub">@{{ formatHistoryTime(item.timestamp) }}</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-else>
                            <div class="canva-section">
                                <h3>Product Mockups</h3>
                                <div class="editor-product-grid">
                                    <button v-for="product in products"
                                            :key="product.id"
                                            type="button"
                                            class="editor-product-card"
                                            :class="{ 'is-active': product.id === selectedProductId }"
                                            @click="selectProduct(product.id)">
                                        <span class="editor-product-icon" v-if="!isIconImage(product.icon)">@{{ product.icon || product.name?.charAt(0) }}</span>
                                        <img v-else class="editor-product-icon-img" :src="product.icon" :alt="product.name">
                                        <span class="editor-product-name">@{{ product.name }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="canva-section">
                                <h3>Colors</h3>
                                <div class="editor-color-grid">
                                    <button v-for="color in currentProduct.colors"
                                            :key="color.value"
                                            type="button"
                                            class="editor-color-dot"
                                            :class="{ 'is-active': color.value === selectedColor.value }"
                                            :style="{ '--swatch-color': color.value }"
                                            :title="color.name"
                                            @click="selectColor(color)">
                                        <span class="sr-only">@{{ color.name }}</span>
                                    </button>
                                </div>

                                <div class="editor-views-block">
                                    <h3>Views</h3>
                                    <div class="editor-pill-group">
                                        <button v-for="view in currentProduct.views"
                                                :key="view.id"
                                                type="button"
                                                class="editor-pill"
                                                :class="{ 'is-active': view.id === selectedViewId }"
                                                @click="selectView(view.id)">
                                            @{{ view.label }}
                                        </button>
                                    </div>
                                    <div class="editor-area-controls">
                                        <button type="button" class="editor-btn" @click="resetPrintArea">Reset area</button>
                                    </div>
                                </div>
                            </div>

                            <div class="canva-section">
                                <h3>Artwork</h3>
                                <input type="file" class="editor-input" accept="image/*,.svg" @change="handleUpload">
                                <button type="button" class="editor-btn w-full" @click="addText">Add Text</button>
                                <div class="editor-text-controls">
                                    <input type="text" class="editor-input" v-model="textValue" placeholder="Type your text">
                                    <select class="editor-input" v-model="selectedFont">
                                        <option v-for="font in fonts" :key="font" :value="font">@{{ font }}</option>
                                    </select>
                                    <button type="button" class="editor-btn w-full" @click="applyTextFont">Apply Font</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="canva-canvas">
                    <div class="canva-canvas-stage">
                        <div class="mockup-stage artboard" ref="stageRef">
                            <div class="artboard-media">
                                <div v-if="hasPhotoMockup"
                                     class="mockup-color"
                                     :style="{ backgroundColor: selectedColor.value }"></div>
                                <div v-if="hasPhotoMockup" class="mockup-photo">
                                    <img :src="currentMockupImage" alt="Product mockup" class="mockup-photo-img">
                                </div>
                                <template v-else>
                                    <div class="mockup-layer mockup-base"
                                         :style="{ color: selectedColor.value }"
                                         v-html="currentMockupBase"></div>
                                    <div class="mockup-layer mockup-overlay" v-html="currentMockupOverlay"></div>
                                </template>
                            </div>
                            <div class="artboard-canvas">
                                <canvas ref="blendCanvasRef"
                                        class="design-canvas design-canvas-blend"
                                        aria-hidden="true"></canvas>
                                <canvas ref="canvasRef" class="design-canvas" id="design-canvas"></canvas>
                            </div>
                            <div class="canva-page-tools">
                                <button class="canva-icon-btn">⧉</button>
                                <button class="canva-icon-btn">＋</button>
                            </div>
                        </div>
                        <div class="canva-flip-panel">
                            <button type="button" class="canva-flip-arrow" @click="previousView" aria-label="View previous side">↑</button>
                            <button type="button" class="canva-flip-arrow" @click="nextView" aria-label="View next side">↓</button>
                        </div>
                        <div class="canva-add-page">
                            <button class="canva-add-page-main" type="button">+ Add page</button>
                            <button class="canva-add-page-more" type="button">▾</button>
                        </div>
                    </div>
                    <div class="canva-zoom-dock">
                        <button type="button" class="canva-zoom-btn" @click="zoomOut">-</button>
                        <div class="canva-zoom-track">
                            <div class="canva-zoom-fill" :style="{ width: Math.min(100, Math.max(0, (zoom - 0.5) / 2 * 100)) + '%' }"></div>
                        </div>
                        <span>@{{ Math.round(zoom * 100) }}%</span>
                        <button type="button" class="canva-zoom-btn" @click="zoomIn">+</button>
                    </div>
                </main>
            </div>
        </div>
        <script>
            window.DESIGNER_CONFIG = @json($designerConfig);
        </script>
    </body>
</html>
