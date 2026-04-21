<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Remove Background | Nai Prints</title>
        <meta name="description" content="Remove image backgrounds quickly. Upload a photo, generate a clean transparent result, and continue editing for print, social, and ecommerce use.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .rb-page {
                margin: 0 auto;
                max-width: 1200px;
                padding: clamp(0.9rem, 1.8vw, 1.35rem) clamp(0.85rem, 2.2vw, 1.2rem) 4rem;
            }
            .rb-hero {
                border-top: 1px solid rgba(15, 23, 42, 0.12);
                padding-top: 1.4rem;
            }
            .rb-breadcrumbs {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
                font-size: 0.96rem;
            }
            .rb-breadcrumbs a {
                color: #425466;
                text-decoration: none;
            }
            .rb-breadcrumbs a:hover {
                color: #e11d2e;
            }
            .rb-hero h1 {
                color: #1f2937;
                font-size: clamp(2rem, 4.6vw, 4.05rem);
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.05;
                margin: 1.5rem auto 0;
                max-width: 1050px;
                text-align: center;
                text-wrap: balance;
            }
            .rb-hero p {
                color: #334155;
                font-size: clamp(1.02rem, 1.7vw, 1.9rem);
                line-height: 1.45;
                margin: 1.2rem auto 0;
                max-width: 1080px;
                text-align: center;
                text-wrap: pretty;
            }
            .rb-workspace {
                align-items: stretch;
                display: grid;
                gap: 1.6rem;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.22fr);
                margin-top: 2.5rem;
            }
            .rb-preview-card,
            .rb-uploader-card,
            .rb-content-card,
            .rb-steps {
                background: #ffffff;
                border: 1px solid rgba(148, 163, 184, 0.24);
                border-radius: 1.5rem;
                box-shadow: 0 22px 40px -34px rgba(15, 23, 42, 0.45);
            }
            .rb-preview-card {
                padding: 1rem 1rem 1.2rem;
            }
            .rb-card-kicker {
                color: var(--brand-red, #e11d2e);
                font-size: 0.86rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                margin: 0 0 0.75rem;
                text-transform: uppercase;
            }
            .rb-preview-stage {
                align-items: center;
                background:
                    radial-gradient(circle at 18% 22%, rgba(225, 29, 46, 0.16) 0%, rgba(225, 29, 46, 0.03) 40%, rgba(255, 255, 255, 0.95) 72%),
                    radial-gradient(circle at 82% 78%, rgba(245, 158, 11, 0.18) 0%, rgba(245, 158, 11, 0.03) 45%, rgba(255, 255, 255, 0.96) 76%);
                border-radius: 1.25rem;
                display: grid;
                min-height: clamp(270px, 42vw, 455px);
                overflow: hidden;
                place-items: center;
                position: relative;
            }
            .rb-preview-stage::before {
                background-image:
                    linear-gradient(45deg, rgba(15, 23, 42, 0.06) 25%, transparent 25%),
                    linear-gradient(-45deg, rgba(15, 23, 42, 0.06) 25%, transparent 25%),
                    linear-gradient(45deg, transparent 75%, rgba(15, 23, 42, 0.06) 75%),
                    linear-gradient(-45deg, transparent 75%, rgba(15, 23, 42, 0.06) 75%);
                background-position: 0 0, 0 10px, 10px -10px, -10px 0;
                background-size: 20px 20px;
                content: '';
                inset: 0;
                opacity: 0.16;
                position: absolute;
            }
            .rb-preview-stage img {
                height: 100%;
                max-height: clamp(270px, 42vw, 455px);
                object-fit: contain;
                position: relative;
                width: 100%;
                z-index: 1;
            }
            .rb-preview-placeholder {
                color: #475569;
                font-size: 1rem;
                font-weight: 700;
                padding: 0 1rem;
                position: relative;
                text-align: center;
                z-index: 1;
            }
            .rb-preview-caption {
                color: #475569;
                font-size: 0.95rem;
                margin: 0.8rem 0 0;
            }
            .rb-uploader-card {
                padding: 1.1rem;
            }
            .rb-dropzone {
                align-items: center;
                border: 4px dashed rgba(225, 29, 46, 0.35);
                border-radius: 1.4rem;
                display: grid;
                gap: 1rem;
                min-height: clamp(280px, 33vw, 344px);
                padding: 2.2rem 1.3rem;
                place-content: center;
                text-align: center;
                transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            }
            .rb-dropzone.is-drag {
                background: rgba(225, 29, 46, 0.05);
                border-color: var(--brand-red, #e11d2e);
                transform: scale(0.995);
            }
            .rb-drop-title {
                color: #1f2937;
                font-size: clamp(1.7rem, 3.2vw, 2.8rem);
                font-weight: 800;
                line-height: 1.15;
                margin: 0;
            }
            .rb-drop-title br {
                display: block;
            }
            .rb-browse {
                background: transparent;
                border: 0;
                color: var(--brand-red, #e11d2e);
                cursor: pointer;
                font: inherit;
                font-weight: 800;
                padding: 0;
                text-decoration: none;
            }
            .rb-browse:hover {
                color: var(--brand-deep, #0b1026);
            }
            .rb-upload-btn,
            .rb-download-btn,
            .rb-reset-btn {
                border-radius: 999px;
                cursor: pointer;
                display: inline-flex;
                font-size: 1.02rem;
                font-weight: 700;
                justify-content: center;
                min-height: 3rem;
                min-width: 180px;
                max-width: 100%;
                padding: 0.7rem 1.3rem;
                text-decoration: none;
                transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
            }
            .rb-upload-btn,
            .rb-download-btn {
                background: linear-gradient(135deg, var(--brand-red, #e11d2e) 0%, var(--brand-deep, #0b1026) 100%);
                border: 0;
                box-shadow: 0 14px 22px -16px rgba(225, 29, 46, 0.85);
                color: #ffffff;
            }
            .rb-upload-btn:hover,
            .rb-download-btn:hover {
                transform: translateY(-1px);
            }
            .rb-upload-btn:disabled {
                cursor: wait;
                opacity: 0.7;
            }
            .rb-meta {
                color: #334155;
                font-size: 0.93rem;
                margin: 0;
            }
            .rb-perks {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 0.8rem;
                justify-content: center;
            }
            .rb-perk {
                align-items: center;
                color: #0f172a;
                display: inline-flex;
                font-size: 0.95rem;
                font-weight: 600;
                gap: 0.35rem;
            }
            .rb-perk-dot {
                background: var(--brand-red, #e11d2e);
                border-radius: 999px;
                color: #ffffff;
                display: inline-grid;
                height: 1.25rem;
                place-items: center;
                width: 1.25rem;
            }
            .rb-result {
                margin-top: 1rem;
                padding: 0.2rem;
            }
            .rb-result h2 {
                color: #0f172a;
                font-size: 1.15rem;
                font-weight: 700;
                margin: 0 0 0.7rem;
            }
            .rb-result-media {
                background: #f8fafc;
                border-radius: 1rem;
                display: grid;
                margin-bottom: 0.85rem;
                min-height: 170px;
                overflow: hidden;
                place-items: center;
            }
            .rb-result-media img {
                max-height: 310px;
                object-fit: contain;
                width: 100%;
            }
            .rb-result-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            .rb-result-actions > * {
                flex: 1 1 180px;
            }
            .rb-reset-btn {
                background: #ffffff;
                border: 1px solid rgba(100, 116, 139, 0.45);
                color: #334155;
            }
            .rb-reset-btn:hover {
                border-color: var(--brand-red, #e11d2e);
                color: var(--brand-red, #e11d2e);
            }
            .rb-content {
                display: grid;
                gap: 1rem;
                margin-top: 2.3rem;
            }
            .rb-content-top {
                text-align: center;
            }
            .rb-content-top h2 {
                color: #0f172a;
                font-size: clamp(1.6rem, 3vw, 2.8rem);
                font-weight: 800;
                letter-spacing: -0.02em;
                margin: 0.35rem 0 0;
            }
            .rb-content-top p {
                color: #475569;
                margin: 0.7rem auto 0;
                max-width: 760px;
            }
            .rb-content-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .rb-content-card {
                padding: 1.1rem;
            }
            .rb-content-card h3 {
                color: #0f172a;
                font-size: 1.12rem;
                font-weight: 700;
                margin: 0;
            }
            .rb-content-card p {
                color: #475569;
                margin: 0.6rem 0 0;
            }
            .rb-steps {
                padding: 1.2rem;
            }
            .rb-steps h3 {
                color: #0f172a;
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0 0 0.9rem;
            }
            .rb-steps ol {
                color: #334155;
                display: grid;
                gap: 0.75rem;
                margin: 0;
                padding-left: 1.25rem;
            }
            .rb-steps li {
                line-height: 1.45;
            }
            @media (max-width: 1200px) {
                .rb-workspace {
                    gap: 1.2rem;
                }
            }
            @media (max-width: 1024px) {
                .rb-workspace {
                    grid-template-columns: 1fr;
                }
                .rb-preview-stage {
                    min-height: clamp(250px, 56vw, 370px);
                }
                .rb-preview-stage img {
                    max-height: clamp(250px, 56vw, 370px);
                }
            }
            @media (max-width: 768px) {
                .rb-page {
                    padding-bottom: 5rem;
                }
                .rb-breadcrumbs {
                    font-size: 0.88rem;
                    gap: 0.45rem;
                }
                .rb-hero h1 {
                    font-size: clamp(1.9rem, 10vw, 2.8rem);
                    line-height: 1.1;
                    margin-top: 1.1rem;
                }
                .rb-hero p {
                    font-size: 1.04rem;
                    line-height: 1.5;
                    margin-top: 0.95rem;
                }
                .rb-workspace {
                    margin-top: 1.6rem;
                }
                .rb-preview-card,
                .rb-uploader-card,
                .rb-content-card,
                .rb-steps {
                    border-radius: 1.1rem;
                }
                .rb-preview-card,
                .rb-uploader-card {
                    padding: 0.8rem;
                }
                .rb-dropzone {
                    border-width: 3px;
                    min-height: auto;
                    padding: 1.45rem 0.85rem;
                }
                .rb-content-grid {
                    grid-template-columns: 1fr;
                }
                .rb-drop-title {
                    font-size: clamp(1.3rem, 7.8vw, 1.85rem);
                    line-height: 1.2;
                }
                .rb-drop-title br {
                    display: none;
                }
                .rb-upload-btn,
                .rb-download-btn,
                .rb-reset-btn {
                    min-width: 0;
                    width: 100%;
                }
                .rb-result-actions {
                    display: grid;
                    grid-template-columns: 1fr;
                }
                .remove-bg-page .purchase-popup {
                    display: none !important;
                }
                .remove-bg-page .whatsapp-float {
                    bottom: 12px;
                    right: 12px;
                }
            }
        </style>
    </head>
    <body class="bg-sand text-ink remove-bg-page">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="rb-page">
                <section class="rb-hero">
                    <nav class="rb-breadcrumbs" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}">Home</a>
                        <span>/</span>
                        <span>Feature</span>
                        <span>/</span>
                        <span aria-current="page">Remove Background</span>
                    </nav>
                    <h1>Remove the background from your image for free.</h1>
                    <p>
                        Quickly clear unwanted backgrounds and keep only the subject. Use the transparent result for product photos,
                        social media creatives, print-ready branding, and cleaner mockups.
                    </p>
                </section>

                <section class="rb-workspace">
                    <article class="rb-preview-card">
                        <p class="rb-card-kicker">Original Preview</p>
                        <div class="rb-preview-stage">
                            <img src="" alt="Uploaded preview image" data-rb-before hidden>
                            <p class="rb-preview-placeholder" data-rb-placeholder>
                                Upload an image to preview your original photo here.
                            </p>
                        </div>
                        <p class="rb-preview-caption" data-rb-status>
                            Select an image to start removing the background.
                        </p>
                    </article>

                    <article class="rb-uploader-card">
                        <div class="rb-dropzone" data-rb-dropzone tabindex="0" role="button" aria-label="Upload image area">
                            <input class="sr-only" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" data-rb-input>
                            <p class="rb-drop-title">
                                Drag and drop an image<br>
                                or <button class="rb-browse" type="button" data-rb-browse>browse to upload.</button>
                            </p>
                            <button class="rb-upload-btn" type="button" data-rb-upload-btn>Upload your photo</button>
                            <p class="rb-meta">File must be JPEG, JPG, PNG or WebP and up to 40MB.</p>
                            <div class="rb-perks">
                                <span class="rb-perk"><span class="rb-perk-dot">✓</span> Free to use</span>
                                <span class="rb-perk"><span class="rb-perk-dot">✓</span> No credit card required</span>
                            </div>
                        </div>

                        <section class="rb-result" data-rb-result hidden>
                            <h2>Background Removed</h2>
                            <div class="rb-result-media">
                                <img src="" alt="Background removed result" data-rb-after>
                            </div>
                            <div class="rb-result-actions">
                                <a class="rb-download-btn" href="#" download="background-removed.png" data-rb-download>
                                    Download PNG
                                </a>
                                <button class="rb-reset-btn" type="button" data-rb-reset>Try another image</button>
                            </div>
                        </section>
                    </article>
                </section>

                <section class="rb-content">
                    <div class="rb-content-top">
                        <p class="rb-card-kicker">Background Removal</p>
                        <h2>Create polished visuals in minutes</h2>
                        <p>
                            After removing your background, you can place your subject on brand colors, catalog layouts,
                            ad creatives, or transparent overlays without distracting edges.
                        </p>
                    </div>

                    <div class="rb-content-grid">
                        <article class="rb-content-card">
                            <h3>Ecommerce Listings</h3>
                            <p>
                                Use clean transparent product shots for marketplaces, online stores, and seasonal catalog updates.
                            </p>
                        </article>
                        <article class="rb-content-card">
                            <h3>Social Media Creative</h3>
                            <p>
                                Drop people or products onto campaign backgrounds for reels, ads, thumbnails, and launch posts.
                            </p>
                        </article>
                        <article class="rb-content-card">
                            <h3>Print and Branding</h3>
                            <p>
                                Prepare logos, portraits, and custom art for t-shirts, posters, flyers, and branded packaging.
                            </p>
                        </article>
                    </div>

                    <article class="rb-steps">
                        <h3>How it works</h3>
                        <ol>
                            <li>Upload a JPEG, PNG, or WebP file from your device.</li>
                            <li>The tool detects edge-connected background tones and removes them.</li>
                            <li>Preview and download a transparent PNG ready for editing or production.</li>
                            <li>Use the output in your mockups, product pages, and design layouts.</li>
                        </ol>
                    </article>
                </section>
            </main>

            @include('partials.site-footer', [
                'globalFooter' => $globalFooter ?? [],
                'globalPurchasePopups' => $globalPurchasePopups ?? [],
                'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
            ])
        </div>

        <script>
            (() => {
                const MAX_FILE_SIZE = 40 * 1024 * 1024;
                const allowedMime = new Set(['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
                const dropzone = document.querySelector('[data-rb-dropzone]');
                const input = document.querySelector('[data-rb-input]');
                const browseBtn = document.querySelector('[data-rb-browse]');
                const uploadBtn = document.querySelector('[data-rb-upload-btn]');
                const beforeImg = document.querySelector('[data-rb-before]');
                const afterImg = document.querySelector('[data-rb-after]');
                const placeholder = document.querySelector('[data-rb-placeholder]');
                const resultBlock = document.querySelector('[data-rb-result]');
                const downloadLink = document.querySelector('[data-rb-download]');
                const resetBtn = document.querySelector('[data-rb-reset]');
                const status = document.querySelector('[data-rb-status]');

                const setStatus = (message) => {
                    if (status) {
                        status.textContent = message;
                    }
                };

                const setBusy = (busy) => {
                    if (!uploadBtn) {
                        return;
                    }
                    uploadBtn.disabled = busy;
                    uploadBtn.textContent = busy ? 'Processing...' : 'Upload your photo';
                };

                const isAllowedFile = (file) => {
                    const mimeType = String(file?.type || '').toLowerCase();
                    return allowedMime.has(mimeType);
                };

                const readFileAsDataUrl = (file) => new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = (event) => resolve(String(event.target?.result || ''));
                    reader.onerror = () => reject(new Error('Unable to read the selected file.'));
                    reader.readAsDataURL(file);
                });

                const removeBackgroundFromRasterSource = (source) => new Promise((resolve, reject) => {
                    const image = new Image();
                    image.crossOrigin = 'anonymous';
                    image.onload = () => {
                        try {
                            const width = image.naturalWidth || image.width;
                            const height = image.naturalHeight || image.height;
                            if (!width || !height) {
                                reject(new Error('This image has invalid dimensions.'));
                                return;
                            }

                            const tempCanvas = document.createElement('canvas');
                            tempCanvas.width = width;
                            tempCanvas.height = height;
                            const ctx = tempCanvas.getContext('2d', { willReadFrequently: true });
                            if (!ctx) {
                                reject(new Error('Canvas is not available in this browser.'));
                                return;
                            }

                            ctx.drawImage(image, 0, 0, width, height);
                            const imageData = ctx.getImageData(0, 0, width, height);
                            const pixels = imageData.data;
                            const pixelCount = width * height;
                            const queue = new Int32Array(pixelCount);

                            const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
                            const getOffset = (idx) => idx * 4;
                            const colorDistanceToBg = (idx, bgR, bgG, bgB) => {
                                const offset = getOffset(idx);
                                const dr = pixels[offset] - bgR;
                                const dg = pixels[offset + 1] - bgG;
                                const db = pixels[offset + 2] - bgB;
                                return Math.sqrt((dr * dr) + (dg * dg) + (db * db));
                            };

                            let sumR = 0;
                            let sumG = 0;
                            let sumB = 0;
                            let sampleCount = 0;

                            const sampleBorderPixel = (idx) => {
                                const offset = getOffset(idx);
                                const alpha = pixels[offset + 3];
                                if (alpha < 10) {
                                    return;
                                }
                                sumR += pixels[offset];
                                sumG += pixels[offset + 1];
                                sumB += pixels[offset + 2];
                                sampleCount += 1;
                            };

                            for (let x = 0; x < width; x += 1) {
                                sampleBorderPixel(x);
                                sampleBorderPixel((height - 1) * width + x);
                            }
                            for (let y = 1; y < height - 1; y += 1) {
                                sampleBorderPixel(y * width);
                                sampleBorderPixel(y * width + (width - 1));
                            }

                            if (!sampleCount) {
                                reject(new Error('Unable to detect a background region from this image.'));
                                return;
                            }

                            const bgR = sumR / sampleCount;
                            const bgG = sumG / sampleCount;
                            const bgB = sumB / sampleCount;

                            let deviation = 0;
                            const measureDeviation = (idx) => {
                                const offset = getOffset(idx);
                                const alpha = pixels[offset + 3];
                                if (alpha < 10) {
                                    return;
                                }
                                const dr = pixels[offset] - bgR;
                                const dg = pixels[offset + 1] - bgG;
                                const db = pixels[offset + 2] - bgB;
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

                            const avgDeviation = deviation / sampleCount;
                            const hardTolerance = clamp(18 + avgDeviation * 1.4, 22, 92);
                            const softTolerance = clamp(hardTolerance + 24, 46, 132);
                            const bgMask = new Uint8Array(pixelCount);

                            const isBackgroundLike = (idx, tolerance = softTolerance) => {
                                const offset = getOffset(idx);
                                const alpha = pixels[offset + 3];
                                if (alpha === 0) {
                                    return true;
                                }
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                const nearWhite = r > 245 && g > 245 && b > 245;
                                const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                return distance <= tolerance || (nearWhite && distance <= tolerance + 14);
                            };

                            let head = 0;
                            let tail = 0;
                            const enqueue = (idx) => {
                                if (idx < 0 || idx >= pixelCount || bgMask[idx]) {
                                    return;
                                }
                                if (!isBackgroundLike(idx, softTolerance)) {
                                    return;
                                }
                                bgMask[idx] = 1;
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
                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                if (x > 0) enqueue(idx - 1);
                                if (x < width - 1) enqueue(idx + 1);
                                if (y > 0) enqueue(idx - width);
                                if (y < height - 1) enqueue(idx + width);
                            }

                            // Clear small leftover background islands that often remain around text/wording shapes.
                            const checked = new Uint8Array(pixelCount);
                            const islandLimit = Math.max(14, Math.min(2400, Math.round(pixelCount * 0.0012)));

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (bgMask[idx] || checked[idx] || !isBackgroundLike(idx, hardTolerance * 0.92)) {
                                    continue;
                                }

                                let componentHead = 0;
                                let componentTail = 0;
                                queue[componentTail] = idx;
                                componentTail += 1;
                                checked[idx] = 1;
                                const component = [idx];

                                while (componentHead < componentTail) {
                                    const current = queue[componentHead];
                                    componentHead += 1;
                                    const x = current % width;
                                    const y = Math.floor(current / width);
                                    const neighbors = [];
                                    if (x > 0) neighbors.push(current - 1);
                                    if (x < width - 1) neighbors.push(current + 1);
                                    if (y > 0) neighbors.push(current - width);
                                    if (y < height - 1) neighbors.push(current + width);

                                    for (let n = 0; n < neighbors.length; n += 1) {
                                        const neighbor = neighbors[n];
                                        if (bgMask[neighbor] || checked[neighbor]) {
                                            continue;
                                        }
                                        if (!isBackgroundLike(neighbor, hardTolerance * 0.92)) {
                                            continue;
                                        }
                                        checked[neighbor] = 1;
                                        queue[componentTail] = neighbor;
                                        componentTail += 1;
                                        component.push(neighbor);
                                    }
                                }

                                if (component.length <= islandLimit) {
                                    for (let i = 0; i < component.length; i += 1) {
                                        bgMask[component[i]] = 1;
                                    }
                                }
                            }

                            const countBgNeighbors = (x, y) => {
                                let count = 0;
                                for (let oy = -1; oy <= 1; oy += 1) {
                                    for (let ox = -1; ox <= 1; ox += 1) {
                                        if (ox === 0 && oy === 0) {
                                            continue;
                                        }
                                        const nx = x + ox;
                                        const ny = y + oy;
                                        if (nx < 0 || ny < 0 || nx >= width || ny >= height) {
                                            continue;
                                        }
                                        if (bgMask[ny * width + nx]) {
                                            count += 1;
                                        }
                                    }
                                }
                                return count;
                            };

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                const offset = getOffset(idx);
                                let alpha = pixels[offset + 3];
                                if (bgMask[idx]) {
                                    pixels[offset + 3] = 0;
                                    continue;
                                }
                                if (alpha === 0) {
                                    continue;
                                }

                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                const bgNeighbors = countBgNeighbors(x, y);
                                const distance = colorDistanceToBg(idx, bgR, bgG, bgB);

                                if (bgNeighbors > 0 && distance <= softTolerance + 8) {
                                    const softness = clamp((softTolerance - distance) / Math.max(1, softTolerance - hardTolerance), 0, 1);
                                    const edgeFactor = bgNeighbors >= 5 ? 0.95 : bgNeighbors >= 3 ? 0.8 : 0.62;
                                    alpha = Math.round(alpha * (1 - softness * edgeFactor));
                                }
                                if (distance <= hardTolerance * 0.86 && bgNeighbors >= 2) {
                                    alpha = Math.min(alpha, 24);
                                }
                                if (alpha < 10) {
                                    alpha = 0;
                                }
                                pixels[offset + 3] = alpha;

                                if (alpha > 0 && alpha < 255) {
                                    // Decontaminate edge colors so PNG halos are reduced.
                                    const a = alpha / 255;
                                    const restore = (channel, bgChannel) =>
                                        clamp(Math.round((channel - bgChannel * (1 - a)) / Math.max(0.08, a)), 0, 255);
                                    pixels[offset] = restore(pixels[offset], bgR);
                                    pixels[offset + 1] = restore(pixels[offset + 1], bgG);
                                    pixels[offset + 2] = restore(pixels[offset + 2], bgB);
                                }
                            }

                            ctx.putImageData(imageData, 0, 0);
                            resolve(tempCanvas.toDataURL('image/png'));
                        } catch (error) {
                            reject(error);
                        }
                    };
                    image.onerror = () => reject(new Error('Could not load this image.'));
                    image.src = source;
                });

                const processFile = async (file) => {
                    if (!file) {
                        return;
                    }
                    if (!isAllowedFile(file)) {
                        window.alert('Please upload a JPEG, JPG, PNG, or WebP file.');
                        return;
                    }
                    if (file.size > MAX_FILE_SIZE) {
                        window.alert('The file is too large. Use an image up to 40MB.');
                        return;
                    }

                    setBusy(true);
                    setStatus('Preparing image...');
                    try {
                        const inputSource = await readFileAsDataUrl(file);
                        if (beforeImg) {
                            beforeImg.src = inputSource;
                            beforeImg.hidden = false;
                        }
                        if (placeholder) {
                            placeholder.hidden = true;
                        }
                        setStatus('Removing background...');
                        const resultSource = await removeBackgroundFromRasterSource(inputSource);
                        if (afterImg) {
                            afterImg.src = resultSource;
                        }
                        if (downloadLink) {
                            downloadLink.href = resultSource;
                        }
                        if (resultBlock) {
                            resultBlock.hidden = false;
                        }
                        setStatus('Background removed. Download your transparent PNG below.');
                    } catch (error) {
                        const message = error?.message || 'Unable to remove background right now.';
                        window.alert(message);
                        setStatus('Something went wrong. Try another image.');
                    } finally {
                        setBusy(false);
                    }
                };

                browseBtn?.addEventListener('click', (event) => {
                    event.preventDefault();
                    input?.click();
                });
                uploadBtn?.addEventListener('click', () => input?.click());
                dropzone?.addEventListener('click', (event) => {
                    const target = event.target;
                    if (target instanceof HTMLElement && target.closest('[data-rb-browse], [data-rb-upload-btn]')) {
                        return;
                    }
                    input?.click();
                });

                input?.addEventListener('change', (event) => {
                    const files = Array.from(event.target?.files || []);
                    const [file] = files;
                    processFile(file);
                    input.value = '';
                });

                dropzone?.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        input?.click();
                    }
                });

                ['dragenter', 'dragover'].forEach((eventName) => {
                    dropzone?.addEventListener(eventName, (event) => {
                        event.preventDefault();
                        dropzone.classList.add('is-drag');
                    });
                });
                ['dragleave', 'drop'].forEach((eventName) => {
                    dropzone?.addEventListener(eventName, (event) => {
                        event.preventDefault();
                        dropzone.classList.remove('is-drag');
                    });
                });
                dropzone?.addEventListener('drop', (event) => {
                    const files = Array.from(event.dataTransfer?.files || []);
                    const [file] = files;
                    processFile(file);
                });

                resetBtn?.addEventListener('click', () => {
                    if (beforeImg) {
                        beforeImg.src = '';
                        beforeImg.hidden = true;
                    }
                    if (afterImg) {
                        afterImg.src = '';
                    }
                    if (downloadLink) {
                        downloadLink.href = '#';
                    }
                    if (resultBlock) {
                        resultBlock.hidden = true;
                    }
                    if (placeholder) {
                        placeholder.hidden = false;
                    }
                    setStatus('Select an image to start removing the background.');
                });
            })();
        </script>
    </body>
</html>
