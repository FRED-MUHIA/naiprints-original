<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.seo', [
            'title' => '3D Jersey Designer | Nai Prints',
            'description' => 'Design football, basketball, rugby, and esports jerseys online with the Nai Prints 3D jersey designer.',
            'canonical' => route('designer.3d'),
        ])
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap">
        <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
        <script>
            window.JERSEY_DESIGNER_BOOT = @json($designerBoot ?? []);
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="jersey-builder-body">
        <div id="jersey-designer-app" class="jersey-builder-shell" v-cloak>
            <header class="jersey-topbar">
                <div class="jersey-topbar-left">
                    <a href="/" class="jersey-brand">
                        <img src="{{ $globalHeader['logo_url'] ?? '/images/logo.svg' }}" alt="Nai Prints">
                        <div>
                            <strong>Nai Prints</strong>
                            <span>3D Jersey Studio</span>
                        </div>
                    </a>
                </div>
                <div class="jersey-topbar-center">
                    <button class="jersey-topbar-btn" type="button" :class="{ 'is-primary': activeWorkspace === 'editor' }" @click="setWorkspace('editor')">Editor</button>
                    <button class="jersey-topbar-btn" type="button" :class="{ 'is-primary': activeWorkspace === 'preview' }" @click="setWorkspace('preview')">Preview Design</button>
                    <button class="jersey-topbar-btn" type="button" @click="undo" :disabled="!canUndo">Undo</button>
                    <button class="jersey-topbar-btn" type="button" @click="redo" :disabled="!canRedo">Redo</button>
                    <button class="jersey-topbar-btn" type="button" @click="saveDesign(false)">Save</button>
                    <button class="jersey-topbar-btn" type="button" @click="exportPng">Export PNG</button>
                    <button class="jersey-topbar-btn" type="button" @click="exportPdf">Export PDF</button>
                    <button class="jersey-topbar-btn is-primary" type="button" @click="placeOrder">Add to Cart</button>
                </div>
                <div class="jersey-topbar-right">
                    <label class="jersey-field-inline">
                        <span>Design ID</span>
                        <input type="text" v-model="loadDesignId" placeholder="Enter design id">
                    </label>
                    <button class="jersey-topbar-btn" type="button" @click="loadDesign">Load</button>
                </div>
            </header>

            <main class="jersey-workspace" v-show="activeWorkspace === 'editor'">
                <aside class="jersey-panel jersey-panel-left">
                    <section class="jersey-card">
                        <p class="jersey-kicker">Jersey Type</p>
                        <div class="jersey-grid">
                            <button
                                v-for="jersey in jerseys"
                                :key="jersey.id"
                                type="button"
                                class="jersey-chip"
                                :class="{ 'is-active': String(jersey.id) === String(selectedJerseyId) }"
                                @click="selectJersey(jersey.id)">
                                @{{ jersey.name }}
                            </button>
                        </div>
                    </section>

                    <section class="jersey-card">
                        <p class="jersey-kicker">Templates</p>
                        <div class="jersey-grid">
                            <button
                                v-for="template in templates"
                                :key="template.key"
                                type="button"
                                class="jersey-chip"
                                :class="{ 'is-active': template.key === selectedTemplate }"
                                @click="applyTemplate(template.key)">
                                @{{ template.label }}
                            </button>
                        </div>
                    </section>

                    <section class="jersey-card">
                        <p class="jersey-kicker">Artboard</p>
                        <div class="jersey-grid">
                            <button
                                v-for="area in availableArtboards"
                                :key="area"
                                type="button"
                                class="jersey-chip"
                                :class="{ 'is-active': area === activeArtboard }"
                                @click="setArtboard(area)">
                                @{{ prettyArea(area) }}
                            </button>
                        </div>
                    </section>
                </aside>

                <section class="jersey-center">
                    <div class="jersey-viewer-wrap">
                        <div class="jersey-viewer-toolbar">
                            <button type="button" class="jersey-chip" @click="focusView('front')">Front</button>
                            <button type="button" class="jersey-chip" @click="focusView('back')">Back</button>
                            <button v-if="supportsSleeves" type="button" class="jersey-chip" @click="focusView('sleeve_left')">Sleeve</button>
                            <button type="button" class="jersey-chip" :class="{ 'is-active': renderStyle === 'wireframe' }" @click="toggleRenderStyle">
                                Render: @{{ renderStyleLabel }}
                            </button>
                            <button type="button" class="jersey-chip" @click="toggleLightingMode">
                                Mode: @{{ lightingModeLabel }}
                            </button>
                        </div>
                        <div ref="threeMountRef" class="jersey-viewer"></div>
                        <div class="jersey-view-meta">
                            <span>Rotate 360° · Zoom · HDR Lighting</span>
                            <span>@{{ mockupStatus || saveStatus }}</span>
                        </div>
                    </div>
                    <div class="jersey-bottom-toggle">
                        <button
                            v-for="view in availableViews"
                            :key="view"
                            type="button"
                            class="jersey-bottom-btn"
                            :class="{ 'is-active': activeView === view }"
                            @click="setView(view)">
                            @{{ prettyArea(view) }}
                        </button>
                    </div>
                </section>

                <aside class="jersey-panel jersey-panel-right">
                    <section class="jersey-card">
                        <p class="jersey-kicker">Colors</p>
                        <label class="jersey-field">
                            <span>Body</span>
                            <input type="color" v-model="colors.body" @input="applyColors">
                        </label>
                        <label class="jersey-field">
                            <span>Sleeve</span>
                            <input type="color" v-model="colors.sleeve" @input="applyColors">
                        </label>
                        <label class="jersey-field">
                            <span>Collar</span>
                            <input type="color" v-model="colors.collar" @input="applyColors">
                        </label>
                    </section>

                    <section class="jersey-card">
                        <p class="jersey-kicker">Player Name & Number</p>
                        <label class="jersey-field">
                            <span>Name</span>
                            <input type="text" v-model="player.name" placeholder="PLAYER NAME">
                        </label>
                        <label class="jersey-field">
                            <span>Number</span>
                            <input type="text" v-model="player.number" placeholder="10">
                        </label>
                        <label class="jersey-field">
                            <span>Font</span>
                            <select v-model="player.fontFamily">
                                <option v-for="font in sportsFonts" :key="font" :value="font">@{{ font }}</option>
                            </select>
                        </label>
                        <label class="jersey-field">
                            <span>Text Color</span>
                            <input type="color" v-model="player.color">
                        </label>
                        <label class="jersey-field">
                            <span>Outline</span>
                            <input type="color" v-model="player.outline">
                        </label>
                        <label class="jersey-field">
                            <span>Size</span>
                            <input type="number" min="28" max="220" v-model.number="player.size">
                        </label>
                        <label class="jersey-field">
                            <span>Spacing</span>
                            <input type="number" min="0" max="300" v-model.number="player.spacing">
                        </label>
                        <button type="button" class="jersey-chip is-active" @click="applyPlayerText">Apply Text</button>
                    </section>

                    <section class="jersey-card">
                        <p class="jersey-kicker">Logo & Sponsors</p>
                        <input type="file" accept="image/*,.svg" @change="uploadLogo">
                        <p class="jersey-note">Drag logos on the artboard. Resize, rotate, and align with guides.</p>
                    </section>

                    <section class="jersey-card">
                        <p class="jersey-kicker">Order</p>
                        <label class="jersey-field">
                            <span>Customer Name</span>
                            <input type="text" v-model="orderCustomer.name" placeholder="Team manager name">
                        </label>
                        <label class="jersey-field">
                            <span>Email</span>
                            <input type="email" v-model="orderCustomer.email" placeholder="name@club.com">
                        </label>
                        <label class="jersey-field">
                            <span>Phone</span>
                            <input type="text" v-model="orderCustomer.phone" placeholder="+254 7xx xxx xxx">
                        </label>
                        <div class="jersey-size-grid">
                            <label v-for="size in sizeOptions" :key="size" class="jersey-field-inline">
                                <span>@{{ size }}</span>
                                <input type="number" min="0" v-model.number="sizeQuantities[size]">
                            </label>
                        </div>
                        <p class="jersey-note">Unit: KES @{{ unitPrice.toFixed(2) }} · Qty: @{{ totalQuantity }} · Total: KES @{{ totalPrice.toFixed(2) }}</p>
                    </section>
                </aside>
            </main>

            <section class="jersey-artboard-section" v-show="activeWorkspace === 'editor'">
                <div class="jersey-artboard-toolbar">
                    <strong>2D Artboard + 3D Sync</strong>
                    <button class="jersey-topbar-btn" type="button" @click="clearActiveArtboard">Clear @{{ prettyArea(activeArtboard) }}</button>
                </div>
                <div class="jersey-artboard-canvases">
                    <div class="jersey-artboard-wrap" data-area="front" :class="{ 'is-active': activeArtboard === 'front' }">
                        <canvas ref="frontCanvasRef" width="1024" height="1024"></canvas>
                    </div>
                    <div class="jersey-artboard-wrap" data-area="back" :class="{ 'is-active': activeArtboard === 'back' }">
                        <canvas ref="backCanvasRef" width="1024" height="1024"></canvas>
                    </div>
                    <div class="jersey-artboard-wrap" data-area="sleeve" :class="{ 'is-active': activeArtboard === 'sleeve' }">
                        <canvas ref="sleeveCanvasRef" width="1024" height="1024"></canvas>
                    </div>
                </div>
            </section>

            <section class="jersey-preview-section" v-show="activeWorkspace === 'preview'">
                <article class="jersey-preview-card">
                    <div class="jersey-preview-pill">POLA</div>
                    <div class="jersey-preview-pattern-grid">
                        <div class="jersey-preview-pattern-slot">
                            <img v-if="previewImages.front" :src="previewImages.front" alt="Front pattern preview">
                            <span v-else>Front pattern</span>
                        </div>
                        <div class="jersey-preview-pattern-slot">
                            <img v-if="previewImages.back" :src="previewImages.back" alt="Back pattern preview">
                            <span v-else>Back pattern</span>
                        </div>
                        <div class="jersey-preview-pattern-slot" v-if="supportsSleeves">
                            <img v-if="previewImages.sleeve" :src="previewImages.sleeve" alt="Sleeve pattern preview">
                            <span v-else>Sleeve pattern</span>
                        </div>
                    </div>
                </article>

                <article class="jersey-preview-card">
                    <div class="jersey-preview-pill">3D PREVIEW</div>
                    <div class="jersey-preview-render">
                        <img v-if="previewImages.three" :src="previewImages.three" alt="3D jersey preview">
                        <span v-else>3D preview</span>
                    </div>
                </article>
            </section>
        </div>
    </body>
</html>
