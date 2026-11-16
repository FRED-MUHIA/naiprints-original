<!doctype html>
<html lang="en" class="dtf-studio-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', [
        'title' => 'DTF Halftone Studio | Nai Prints',
        'description' => 'Create print-ready halftone artwork in your browser with Nai Prints DTF Studio.',
        'canonical' => url('/dtf-halftone-studio'),
        'robots' => 'noindex, follow',
    ])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('dtf-studio-assets/studio.css') }}?v={{ filemtime(public_path('dtf-studio-assets/studio.css')) }}">
</head>
<body class="dtf-site-body">
    @include('partials.site-header', ['globalHeader' => $globalHeader ?? [], 'showMenuStrip' => false])
    <main id="dtf-studio" class="dtf-app" data-worker="{{ asset('dtf-studio-assets/worker.js') }}?v={{ filemtime(public_path('dtf-studio-assets/worker.js')) }}" data-trace-worker="{{ asset('dtf-studio-assets/trace-worker.js') }}?v={{ filemtime(public_path('dtf-studio-assets/trace-worker.js')) }}">
        <header class="dtf-toolbar">
            <a class="dtf-brand" href="/" aria-label="Nai Prints home"><span class="dtf-mark">NP</span><span>Nai Prints <small>DTF STUDIO</small></span></a>
            <div class="dtf-actions">
                <button data-action="new">New</button><button data-action="upload">Upload image</button>
                <button data-action="undo" title="Undo (Ctrl+Z)">↶</button><button data-action="redo" title="Redo (Ctrl+Y)">↷</button>
                <button data-action="reset">Reset</button><button data-action="remove-bg">Remove background</button><button data-action="trace">Image trace</button><span class="dtf-divider"></span>
                <button data-action="zoom-out" title="Zoom out">−</button><button data-action="fit">Fit</button><button data-action="actual-size" title="Show the preview at 100%">100%</button><button data-action="zoom-in" title="Zoom in">+</button>
                <button data-action="compare" aria-pressed="false">Before / after</button><button class="dtf-primary" data-action="export">Export</button>
            </div>
        </header>
        <section class="dtf-workspace">
            <aside class="dtf-sidebar">
                <div class="dtf-panel-title">WORKSPACE <span>LOCAL ONLY</span></div>
                <label class="dtf-upload" for="dtf-file"><span>＋</span><b>Drop artwork here</b><small>PNG, JPG, WEBP, TIFF*</small></label>
                <input id="dtf-file" type="file" accept="image/png,image/jpeg,image/webp,image/tiff,.tif,.tiff" hidden>
                <details open><summary>Halftone effects</summary><div class="dtf-effect-grid" id="effect-list"></div></details>
                <details open><summary>Adjustments</summary><div class="dtf-control-list" id="adjustments"></div></details>
                <details><summary>Color & transparency</summary><div class="dtf-control-list" id="color-controls"></div></details>
                <details><summary>Presets</summary><div class="dtf-presets" id="presets"></div><div class="dtf-inline"><button data-action="save-preset">Save preset</button><button data-action="load-project">Open project</button></div></details>
            </aside>
            <section class="dtf-stage" aria-label="Artwork canvas">
                <div class="dtf-stagebar"><span id="image-name">Untitled project</span><div><button data-action="checker" aria-pressed="true">▦</button><button data-action="background-black">Black</button><button data-action="background-white">White</button></div></div>
                <div class="dtf-canvas-wrap" id="canvas-wrap"><div class="dtf-empty" id="empty-state"><div class="dtf-empty-icon">◉</div><h1>Your print, your pattern.</h1><p>Upload a photograph, illustration, or logo to start creating.</p><button class="dtf-primary" data-action="upload">Choose artwork</button><small>Your images stay on this device.</small></div><canvas id="artwork" aria-label="Processed artwork preview"></canvas></div>
                <footer class="dtf-status"><span id="status-text">Ready · Processing stays in your browser</span><span id="dimensions">No image</span><span id="zoom-label">100%</span><span id="output-info">Output 300 DPI</span></footer>
            </section>
            <aside class="dtf-settings"><div class="dtf-panel-title">EFFECT SETTINGS <span id="active-effect">CLASSIC DOT</span></div><div id="effect-settings" class="dtf-control-list"></div><div class="dtf-layer-head"><b>Layers</b><button data-action="add-layer">＋ Add layer</button></div><div id="layer-list" class="dtf-layers"><div class="dtf-layer-empty">Upload artwork to create a layer.</div></div><div class="dtf-layer-tools"><button data-action="duplicate-layer">Duplicate</button><button data-action="delete-layer">Delete</button></div></aside>
        </section>
        <dialog id="export-dialog" class="dtf-dialog"><form method="dialog" id="export-form"><div class="dtf-dialog-head"><div><small>PRINT PRODUCTION</small><h2>Export artwork</h2></div><button value="cancel" aria-label="Close">×</button></div><div class="dtf-export-grid"><label>Width <input id="export-width" type="number" min="1" value="10" step="0.1"></label><label>Height <input id="export-height" type="number" min="1" value="10" step="0.1"></label><label>Units <select id="export-units"><option value="in">Inches</option><option value="cm">Centimetres</option><option value="px">Pixels</option></select></label><label>DPI <input id="export-dpi" type="number" min="72" max="1200" value="300"></label></div><label class="dtf-check"><input id="export-lock" type="checkbox" checked> Keep image proportions</label><label class="dtf-check"><input id="export-transparent" type="checkbox" checked> Transparent background</label><label class="dtf-check"><input id="export-underbase" type="checkbox"> Download separate white-underbase mask</label><p id="export-warning" class="dtf-export-warning"></p><p id="export-size" class="dtf-export-size">Estimated file size will appear here.</p><div class="dtf-dialog-actions"><button value="cancel">Cancel</button><button type="button" id="download-png" class="dtf-primary">Download PNG</button><button type="button" id="download-jpeg">Download JPEG</button><button type="button" id="download-project">Save project JSON</button></div></form></dialog>
        <dialog id="trace-dialog" class="dtf-dialog dtf-trace-dialog"><div class="dtf-dialog-head"><div><small>VECTOR CONVERSION</small><h2>Image trace</h2></div><button type="button" data-trace-close aria-label="Close">×</button></div><div class="dtf-trace-controls"><label>Trace mode<select id="trace-mode"><option value="bw">Black &amp; white</option><option value="3">3-color detector</option><option value="16">16-color detector</option></select></label><label id="trace-threshold-wrap">Black threshold <input id="trace-threshold" type="range" min="0" max="255" value="150"><output id="trace-threshold-value">150</output></label></div><div class="dtf-trace-preview" id="trace-preview"><p>Upload artwork to create a vector preview.</p></div><p class="dtf-trace-info" id="trace-info">SVG output · Background transparency preserved</p><div class="dtf-dialog-actions"><button type="button" data-trace-close>Close</button><button type="button" id="trace-run" class="dtf-primary">Trace preview</button><button type="button" id="trace-download" disabled>Download SVG</button></div></dialog>
        <input id="project-file" type="file" accept="application/json,.json" hidden>
        <input id="custom-color" type="color" value="#ffffff" hidden>
        <div id="toast" class="dtf-toast" role="status" aria-live="polite"></div>
    </main>
    <script src="{{ asset('dtf-studio-assets/studio.js') }}?v={{ filemtime(public_path('dtf-studio-assets/studio.js')) }}" defer></script>
</body>
</html>
