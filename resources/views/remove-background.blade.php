<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.seo', [
            'title' => 'Remove Image Background Online | Nai Prints',
            'description' => 'Remove image backgrounds quickly for print, ecommerce, social media, and product mockups with Nai Prints.',
            'canonical' => route('remove-background'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .rb-page {
                margin: 0 auto;
                max-width: 1200px;
                padding: clamp(0.2rem, 0.8vw, 0.55rem) clamp(0.85rem, 2.2vw, 1.2rem) 4rem;
            }
            .rb-hero {
                padding-top: 0.15rem;
            }
            .rb-hero h1 {
                color: #1f2937;
                font-size: clamp(1.35rem, 2.8vw, 2.75rem);
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.12;
                margin: 0.45rem auto 0;
                max-width: 1050px;
                text-align: center;
                text-wrap: balance;
            }
            .rb-hero p {
                color: #334155;
                font-size: clamp(0.9rem, 1.25vw, 1.45rem);
                line-height: 1.52;
                margin: 1.2rem auto 0;
                max-width: 1080px;
                text-align: center;
                text-wrap: pretty;
            }
            .rb-howto {
                margin-top: 1.6rem;
                border-radius: 1.3rem;
                padding: clamp(1.5rem, 3vw, 3rem) clamp(1rem, 2.4vw, 2rem);
                background:
                    radial-gradient(140% 120% at 0% 100%, rgba(245, 158, 11, 0.25) 0%, transparent 58%),
                    linear-gradient(122deg, var(--brand-deep, #0b1026) 0%, var(--brand-red, #e11d2e) 62%, #f97316 100%);
                box-shadow: 0 20px 42px -30px rgba(225, 29, 46, 0.55);
            }
            .rb-howto-title {
                color: #ffffff;
                font-size: clamp(1.25rem, 3.1vw, 3rem);
                font-weight: 800;
                letter-spacing: -0.02em;
                margin: 0;
                text-align: center;
                text-wrap: balance;
            }
            .rb-howto-grid {
                margin-top: clamp(1rem, 2vw, 2rem);
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .rb-howto-card {
                background: rgba(255, 255, 255, 0.96);
                border-radius: 1rem;
                border: 1px solid rgba(255, 255, 255, 0.62);
                padding: 1.1rem;
                box-shadow: 0 14px 28px -24px rgba(15, 23, 42, 0.4);
            }
            .rb-howto-head {
                align-items: center;
                display: flex;
                gap: 0.65rem;
            }
            .rb-howto-icon-wrap {
                align-items: center;
                background: linear-gradient(145deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.78));
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 0.85rem;
                box-shadow: 0 10px 18px -15px rgba(11, 16, 38, 0.6);
                display: inline-flex;
                flex: 0 0 auto;
                height: 3rem;
                justify-content: center;
                width: 3rem;
            }
            .rb-howto-icon {
                color: var(--brand-red, #e11d2e);
                height: 1.65rem;
                width: 1.65rem;
            }
            .rb-howto-head h3 {
                color: #0f172a;
                font-size: clamp(1rem, 1.4vw, 1.8rem);
                font-weight: 800;
                margin: 0;
            }
            .rb-howto-card p {
                color: #1e293b;
                font-size: 0.95rem;
                line-height: 1.45;
                margin: 0.7rem 0 0;
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
                background-color: #f8fafc;
                background-image:
                    linear-gradient(45deg, rgba(15, 23, 42, 0.08) 25%, transparent 25%),
                    linear-gradient(-45deg, rgba(15, 23, 42, 0.08) 25%, transparent 25%),
                    linear-gradient(45deg, transparent 75%, rgba(15, 23, 42, 0.08) 75%),
                    linear-gradient(-45deg, transparent 75%, rgba(15, 23, 42, 0.08) 75%);
                background-position: 0 0, 0 11px, 11px -11px, -11px 0;
                background-size: 22px 22px;
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
            .rb-reviews {
                margin-top: 2.3rem;
                border: 1px solid rgba(11, 16, 38, 0.14);
                border-radius: 1.25rem;
                background:
                    radial-gradient(130% 115% at 0% 100%, rgba(245, 158, 11, 0.2) 0%, transparent 58%),
                    linear-gradient(120deg, rgba(11, 16, 38, 0.07) 0%, rgba(225, 29, 46, 0.12) 56%, rgba(245, 158, 11, 0.12) 100%);
                overflow: hidden;
                padding: 1.2rem 1rem 1.35rem;
            }
            .rb-reviews-head {
                align-items: center;
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 0.9rem;
            }
            .rb-reviews-title {
                margin: 0;
                color: var(--brand-deep, #0b1026);
                font-size: clamp(1.05rem, 2vw, 1.5rem);
                font-weight: 800;
                letter-spacing: -0.01em;
            }
            .rb-reviews-sub {
                margin: 0;
                color: var(--brand-red, #e11d2e);
                font-size: 0.9rem;
                font-weight: 700;
            }
            .rb-reviews-track {
                display: grid;
                gap: 1rem;
                grid-auto-flow: column;
                grid-auto-columns: minmax(260px, 1fr);
                overflow-x: auto;
                overscroll-behavior-x: contain;
                padding-bottom: 0.4rem;
                scrollbar-width: thin;
            }
            .rb-review-card {
                display: flex;
                flex-direction: column;
                min-height: 320px;
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid rgba(11, 16, 38, 0.08);
                border-radius: 1rem;
                padding: 1rem 1rem 0.9rem;
                box-shadow: 0 16px 34px -28px rgba(11, 16, 38, 0.45);
            }
            .rb-review-stars {
                color: #f59e0b;
                font-size: 1.05rem;
                letter-spacing: 0.18rem;
                margin-bottom: 0.65rem;
                user-select: none;
            }
            .rb-review-title {
                margin: 0;
                color: #1e1b3a;
                font-size: 1.05rem;
                font-weight: 800;
            }
            .rb-review-text {
                color: #1f2937;
                font-size: 0.97rem;
                line-height: 1.5;
                margin: 0.7rem 0 0;
            }
            .rb-review-author {
                margin-top: auto;
                padding-top: 1rem;
                color: #111827;
                font-size: 0.95rem;
                font-weight: 700;
            }
            @media (max-width: 1200px) {
                .rb-workspace {
                    gap: 1.2rem;
                }
            }
            @media (max-width: 1024px) {
                .rb-howto-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
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
                .rb-reviews {
                    padding: 1rem 0.75rem 1.15rem;
                }
                .rb-reviews-track {
                    grid-auto-columns: minmax(238px, 82vw);
                }
                .rb-review-card {
                    min-height: 290px;
                }
                .rb-howto {
                    border-radius: 1.05rem;
                    margin-top: 1.2rem;
                    padding: 1.1rem 0.85rem;
                }
                .rb-howto-grid {
                    grid-template-columns: 1fr;
                }
                .rb-howto-title {
                    font-size: clamp(1.2rem, 8vw, 1.9rem);
                    line-height: 1.15;
                }
                .rb-hero h1 {
                    font-size: clamp(1.25rem, 6.7vw, 1.95rem);
                    line-height: 1.16;
                    margin-top: 0.3rem;
                }
                .rb-hero p {
                    font-size: 0.95rem;
                    line-height: 1.56;
                    margin-top: 0.8rem;
                }
                .rb-workspace {
                    margin-top: 1.6rem;
                }
                .rb-preview-card,
                .rb-uploader-card,
                .rb-content-card,
                .rb-steps,
                .rb-reviews {
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
            @include('partials.site-header', [
                'globalHeader' => $globalHeader ?? [],
                'showMenuStrip' => false,
            ])

            <main class="rb-page">
                <section class="rb-hero">
                    <h1>Remove the background from your image for free.</h1>
                    <p>
                        Quickly clear unwanted backgrounds and keep only the subject with three smart tools: Magic Wand, Quick
                        Selection, and automatic Object Selection. Use the transparent result for product photos, social media
                        creatives, print-ready branding, and cleaner mockups.
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

                <section class="rb-howto" aria-label="How to remove the background">
                    <h2 class="rb-howto-title">Background Remover Tools for Precise Selections.</h2>
                    <div class="rb-howto-grid">
                        <article class="rb-howto-card">
                            <div class="rb-howto-head">
                                <span class="rb-howto-icon-wrap" aria-hidden="true">
                                    <svg class="rb-howto-icon" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 16V6.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                        <path d="M8.7 10.2L12 6.8l3.3 3.4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="4" y="13.6" width="16" height="6.4" rx="2.2" stroke="currentColor" stroke-width="2.2"/>
                                    </svg>
                                </span>
                                <h3>1. Magic Wand Tool</h3>
                            </div>
                            <p>
                                Select similarly colored image areas in one click. This is ideal for flat or consistent background zones.
                            </p>
                        </article>
                        <article class="rb-howto-card">
                            <div class="rb-howto-head">
                                <span class="rb-howto-icon-wrap" aria-hidden="true">
                                    <svg class="rb-howto-icon" viewBox="0 0 24 24" fill="none">
                                        <rect x="3.5" y="4.5" width="17" height="15" rx="3" stroke="currentColor" stroke-width="2.2"/>
                                        <circle cx="16.7" cy="9" r="1.6" fill="currentColor"/>
                                        <path d="M6.7 15.8l3.1-3.1a1.6 1.6 0 0 1 2.2 0l1.5 1.5a1.6 1.6 0 0 0 2.2 0l1.6-1.6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <h3>2. Quick Selection Tool</h3>
                            </div>
                            <p>
                                Make your selection quickly while the tool follows visible image edges to keep the cutout clean.
                            </p>
                        </article>
                        <article class="rb-howto-card">
                            <div class="rb-howto-head">
                                <span class="rb-howto-icon-wrap" aria-hidden="true">
                                    <svg class="rb-howto-icon" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 4.6v9.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                        <path d="M8.6 11.1L12 14.5l3.4-3.4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M5 18h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                        <path d="M7.2 18v1.3h9.6V18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <h3>3. Selection Tool</h3>
                            </div>
                            <p>
                                Automatically find and select the main object, then remove the background with minimal manual edits.
                            </p>
                        </article>
                    </div>
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
                        <h3>How the tools work together</h3>
                        <ol>
                            <li>Upload a JPEG, PNG, or WebP image from your device.</li>
                            <li>Use Magic Wand to select similarly colored background areas.</li>
                            <li>Refine edges with Quick Selection, or auto-pick your subject with Selection Tool.</li>
                            <li>Preview and download a transparent PNG ready for editing or production.</li>
                        </ol>
                    </article>
                </section>

                <section class="rb-reviews" aria-label="User reviews">
                    <div class="rb-reviews-head">
                        <h2 class="rb-reviews-title">What people say about our background remover</h2>
                    </div>
                    <div class="rb-reviews-track">
                        <article class="rb-review-card">
                            <p class="rb-review-stars" aria-hidden="true">★★★★★</p>
                            <h3 class="rb-review-title">Cool app!</h3>
                            <p class="rb-review-text">
                                It was my first time using this tool to remove background. Too easy and super clean output.
                            </p>
                            <p class="rb-review-author">Luis Afonso</p>
                        </article>
                        <article class="rb-review-card">
                            <p class="rb-review-stars" aria-hidden="true">★★★★★</p>
                            <h3 class="rb-review-title">Excellent</h3>
                            <p class="rb-review-text">
                                This is one of the most helpful editing tools I have used. The AI background remover is next-level compared to rivals.
                            </p>
                            <p class="rb-review-author">Bob Flake</p>
                        </article>
                        <article class="rb-review-card">
                            <p class="rb-review-stars" aria-hidden="true">★★★★★</p>
                            <h3 class="rb-review-title">Great for removing backgrounds</h3>
                            <p class="rb-review-text">
                                I remove backgrounds from artwork images all the time, and this is my go-to because results stay sharp.
                            </p>
                            <p class="rb-review-author">Ali Mohamed</p>
                        </article>
                        <article class="rb-review-card">
                            <p class="rb-review-stars" aria-hidden="true">★★★★★</p>
                            <h3 class="rb-review-title">Great!</h3>
                            <p class="rb-review-text">
                                I use this app regularly and it gives me a quality transparent PNG I can publish immediately.
                            </p>
                            <p class="rb-review-author">Yasmin Achieng</p>
                        </article>
                    </div>
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
                            const borderColorBuckets = new Map();

                            const sampleBorderPixel = (idx) => {
                                const offset = getOffset(idx);
                                const alpha = pixels[offset + 3];
                                if (alpha < 10) {
                                    return;
                                }

                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                sumR += r;
                                sumG += g;
                                sumB += b;
                                sampleCount += 1;

                                const bucketKey = `${r >> 4}:${g >> 4}:${b >> 4}`;
                                const bucket = borderColorBuckets.get(bucketKey) || { count: 0, sumR: 0, sumG: 0, sumB: 0 };
                                bucket.count += 1;
                                bucket.sumR += r;
                                bucket.sumG += g;
                                bucket.sumB += b;
                                borderColorBuckets.set(bucketKey, bucket);
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

                            let bgR = sumR / sampleCount;
                            let bgG = sumG / sampleCount;
                            let bgB = sumB / sampleCount;

                            let dominantBucket = null;
                            borderColorBuckets.forEach((bucket) => {
                                if (!dominantBucket || bucket.count > dominantBucket.count) {
                                    dominantBucket = bucket;
                                }
                            });
                            if (dominantBucket && dominantBucket.count >= Math.max(12, Math.round(sampleCount * 0.08))) {
                                bgR = dominantBucket.sumR / dominantBucket.count;
                                bgG = dominantBucket.sumG / dominantBucket.count;
                                bgB = dominantBucket.sumB / dominantBucket.count;
                            }

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
                            const centerMinX = Math.floor(width * 0.14);
                            const centerMaxX = Math.ceil(width * 0.86);
                            const centerMinY = Math.floor(height * 0.04);
                            const centerMaxY = Math.ceil(height * 0.97);
                            const coreMinX = Math.floor(width * 0.16);
                            const coreMaxX = Math.ceil(width * 0.84);
                            const coreMinY = Math.floor(height * 0.05);
                            const coreMaxY = Math.ceil(height * 0.96);
                            const isInCenterBand = (x, y) =>
                                x >= centerMinX && x <= centerMaxX && y >= centerMinY && y <= centerMaxY;
                            const isInCoreBand = (x, y) =>
                                x >= coreMinX && x <= coreMaxX && y >= coreMinY && y <= coreMaxY;
                            const getColorSpread = (r, g, b) => Math.max(r, g, b) - Math.min(r, g, b);

                            const isBackgroundLike = (idx, tolerance = softTolerance, strict = false) => {
                                const offset = getOffset(idx);
                                const alpha = pixels[offset + 3];
                                if (alpha === 0) {
                                    return true;
                                }
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                const nearWhite = r > 245 && g > 245 && b > 245;
                                if (strict) {
                                    const colorSpread = getColorSpread(r, g, b);
                                    const brightness = (r + g + b) / 3;
                                    const nearGray = colorSpread <= 22;
                                    if (!nearWhite && !(nearGray && brightness >= 152)) {
                                        return false;
                                    }
                                }
                                const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                return distance <= tolerance || (nearWhite && distance <= tolerance + 14);
                            };

                            const fillBackgroundMask = (maskHardTolerance, maskSoftTolerance, strict = false) => {
                                const mask = new Uint8Array(pixelCount);
                                const centerTolerance = strict
                                    ? clamp(maskHardTolerance + 2, maskHardTolerance + 1, Math.max(maskHardTolerance + 2, maskSoftTolerance - 2))
                                    : clamp(maskHardTolerance + 12, maskHardTolerance + 4, Math.max(maskHardTolerance + 5, maskSoftTolerance - 8));
                                const coreTolerance = strict
                                    ? clamp(maskHardTolerance + 1, maskHardTolerance, centerTolerance)
                                    : clamp(maskHardTolerance + 6, maskHardTolerance + 2, centerTolerance);

                                let head = 0;
                                let tail = 0;
                                const enqueue = (idx) => {
                                    if (idx < 0 || idx >= pixelCount || mask[idx]) {
                                        return;
                                    }
                                    const x = idx % width;
                                    const y = Math.floor(idx / width);
                                    const tolerance = isInCoreBand(x, y)
                                        ? coreTolerance
                                        : (isInCenterBand(x, y) ? centerTolerance : maskSoftTolerance);
                                    if (!isBackgroundLike(idx, tolerance, strict)) {
                                        return;
                                    }
                                    mask[idx] = 1;
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

                                return mask;
                            };

                            const estimateCoreBackgroundRatio = (mask) => {
                                let corePixels = 0;
                                let coreBackground = 0;
                                for (let y = coreMinY; y <= coreMaxY; y += 1) {
                                    for (let x = coreMinX; x <= coreMaxX; x += 1) {
                                        const idx = (y * width) + x;
                                        corePixels += 1;
                                        if (mask[idx]) {
                                            coreBackground += 1;
                                        }
                                    }
                                }
                                return corePixels ? (coreBackground / corePixels) : 0;
                            };
                            const estimateBackgroundRatio = (mask) => {
                                let count = 0;
                                for (let idx = 0; idx < pixelCount; idx += 1) {
                                    if (mask[idx]) {
                                        count += 1;
                                    }
                                }
                                return pixelCount ? (count / pixelCount) : 0;
                            };
                            const estimateBorderBackgroundRatio = (mask) => {
                                let borderPixels = 0;
                                let borderBackground = 0;
                                for (let x = 0; x < width; x += 1) {
                                    const top = x;
                                    const bottom = ((height - 1) * width) + x;
                                    borderPixels += 2;
                                    if (mask[top]) {
                                        borderBackground += 1;
                                    }
                                    if (mask[bottom]) {
                                        borderBackground += 1;
                                    }
                                }
                                for (let y = 1; y < height - 1; y += 1) {
                                    const left = y * width;
                                    const right = (y * width) + (width - 1);
                                    borderPixels += 2;
                                    if (mask[left]) {
                                        borderBackground += 1;
                                    }
                                    if (mask[right]) {
                                        borderBackground += 1;
                                    }
                                }
                                return borderPixels ? (borderBackground / borderPixels) : 0;
                            };

                            let bgMask = fillBackgroundMask(hardTolerance, softTolerance, false);
                            const coreBackgroundRatio = estimateCoreBackgroundRatio(bgMask);
                            if (coreBackgroundRatio > 0.52) {
                                const conservativeHardTolerance = clamp(hardTolerance * 0.72, 18, 66);
                                const conservativeSoftTolerance = clamp(conservativeHardTolerance + 14, 30, 94);
                                const conservativeMask = fillBackgroundMask(conservativeHardTolerance, conservativeSoftTolerance, true);
                                const conservativeCoreBackgroundRatio = estimateCoreBackgroundRatio(conservativeMask);
                                const conservativeBgRatio = estimateBackgroundRatio(conservativeMask);
                                const conservativeBorderRatio = estimateBorderBackgroundRatio(conservativeMask);
                                const canUseConservativeMask = (
                                    conservativeBgRatio >= 0.12 &&
                                    conservativeBorderRatio >= 0.62
                                );
                                if (canUseConservativeMask && conservativeCoreBackgroundRatio + 0.04 < coreBackgroundRatio) {
                                    bgMask = conservativeMask;
                                }
                            }

                            // Protect likely main subjects so people/primary characters are not over-erased.
                            const protectMask = new Uint8Array(pixelCount);
                            const subjectMask = new Uint8Array(pixelCount);
                            const foregroundSeed = new Uint8Array(pixelCount);
                            const subjectBounds = { minX: width, minY: height, maxX: -1, maxY: -1, has: false };

                            // First keep the strongest foreground components around the center.
                            const subjectVisited = new Uint8Array(pixelCount);
                            const minSubjectComponent = Math.max(260, Math.round(pixelCount * 0.0015));
                            const largeSubjectComponent = Math.max(1400, Math.round(pixelCount * 0.012));
                            const maxSubjectComponents = 3;
                            const subjectCandidates = [];

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (bgMask[idx] || subjectVisited[idx]) {
                                    continue;
                                }
                                const offset = getOffset(idx);
                                if (pixels[offset + 3] < 16) {
                                    continue;
                                }

                                let componentHead = 0;
                                let componentTail = 0;
                                queue[componentTail] = idx;
                                componentTail += 1;
                                subjectVisited[idx] = 1;
                                const component = [idx];
                                let coreHits = 0;
                                let borderHits = 0;

                                while (componentHead < componentTail) {
                                    const current = queue[componentHead];
                                    componentHead += 1;
                                    const x = current % width;
                                    const y = Math.floor(current / width);
                                    if (isInCoreBand(x, y)) {
                                        coreHits += 1;
                                    }
                                    if (x === 0 || y === 0 || x === width - 1 || y === height - 1) {
                                        borderHits += 1;
                                    }

                                    if (x > 0) {
                                        const neighbor = current - 1;
                                        if (!bgMask[neighbor] && !subjectVisited[neighbor]) {
                                            const neighborOffset = getOffset(neighbor);
                                            if (pixels[neighborOffset + 3] >= 16) {
                                                subjectVisited[neighbor] = 1;
                                                queue[componentTail] = neighbor;
                                                componentTail += 1;
                                                component.push(neighbor);
                                            }
                                        }
                                    }
                                    if (x < width - 1) {
                                        const neighbor = current + 1;
                                        if (!bgMask[neighbor] && !subjectVisited[neighbor]) {
                                            const neighborOffset = getOffset(neighbor);
                                            if (pixels[neighborOffset + 3] >= 16) {
                                                subjectVisited[neighbor] = 1;
                                                queue[componentTail] = neighbor;
                                                componentTail += 1;
                                                component.push(neighbor);
                                            }
                                        }
                                    }
                                    if (y > 0) {
                                        const neighbor = current - width;
                                        if (!bgMask[neighbor] && !subjectVisited[neighbor]) {
                                            const neighborOffset = getOffset(neighbor);
                                            if (pixels[neighborOffset + 3] >= 16) {
                                                subjectVisited[neighbor] = 1;
                                                queue[componentTail] = neighbor;
                                                componentTail += 1;
                                                component.push(neighbor);
                                            }
                                        }
                                    }
                                    if (y < height - 1) {
                                        const neighbor = current + width;
                                        if (!bgMask[neighbor] && !subjectVisited[neighbor]) {
                                            const neighborOffset = getOffset(neighbor);
                                            if (pixels[neighborOffset + 3] >= 16) {
                                                subjectVisited[neighbor] = 1;
                                                queue[componentTail] = neighbor;
                                                componentTail += 1;
                                                component.push(neighbor);
                                            }
                                        }
                                    }
                                }

                                if (component.length < minSubjectComponent) {
                                    continue;
                                }
                                const coreStrongEnough = coreHits >= Math.max(18, Math.round(component.length * 0.018));
                                const canKeepLargeCenter = component.length >= largeSubjectComponent && coreHits >= 10;
                                if (!coreStrongEnough && !canKeepLargeCenter) {
                                    continue;
                                }

                                const score = (component.length * 1.35) + (coreHits * 24) - (borderHits * 8);
                                subjectCandidates.push({ component, score });
                            }

                            subjectCandidates.sort((a, b) => b.score - a.score);
                            const pickedSubjectCount = Math.min(maxSubjectComponents, subjectCandidates.length);
                            for (let i = 0; i < pickedSubjectCount; i += 1) {
                                const component = subjectCandidates[i].component;
                                for (let j = 0; j < component.length; j += 1) {
                                    const pixel = component[j];
                                    subjectMask[pixel] = 1;
                                    protectMask[pixel] = 1;
                                    const x = pixel % width;
                                    const y = Math.floor(pixel / width);
                                    subjectBounds.minX = Math.min(subjectBounds.minX, x);
                                    subjectBounds.minY = Math.min(subjectBounds.minY, y);
                                    subjectBounds.maxX = Math.max(subjectBounds.maxX, x);
                                    subjectBounds.maxY = Math.max(subjectBounds.maxY, y);
                                    subjectBounds.has = true;
                                }
                            }

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (bgMask[idx]) {
                                    continue;
                                }
                                const offset = getOffset(idx);
                                if (pixels[offset + 3] < 10) {
                                    continue;
                                }
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                const colorSpread = Math.max(r, g, b) - Math.min(r, g, b);
                                const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                if (distance >= hardTolerance + 18 || colorSpread >= 24) {
                                    foregroundSeed[idx] = 1;
                                }
                            }

                            const seedVisited = new Uint8Array(pixelCount);
                            const minCenterComponent = Math.max(120, Math.round(pixelCount * 0.00028));
                            const largeAnywhereComponent = Math.max(650, Math.round(pixelCount * 0.009));

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (!foregroundSeed[idx] || seedVisited[idx]) {
                                    continue;
                                }

                                let componentHead = 0;
                                let componentTail = 0;
                                queue[componentTail] = idx;
                                componentTail += 1;
                                seedVisited[idx] = 1;
                                const component = [idx];
                                let touchesCenter = false;

                                while (componentHead < componentTail) {
                                    const current = queue[componentHead];
                                    componentHead += 1;
                                    const x = current % width;
                                    const y = Math.floor(current / width);
                                    if (isInCenterBand(x, y)) {
                                        touchesCenter = true;
                                    }

                                    if (x > 0) {
                                        const neighbor = current - 1;
                                        if (foregroundSeed[neighbor] && !seedVisited[neighbor]) {
                                            seedVisited[neighbor] = 1;
                                            queue[componentTail] = neighbor;
                                            componentTail += 1;
                                            component.push(neighbor);
                                        }
                                    }
                                    if (x < width - 1) {
                                        const neighbor = current + 1;
                                        if (foregroundSeed[neighbor] && !seedVisited[neighbor]) {
                                            seedVisited[neighbor] = 1;
                                            queue[componentTail] = neighbor;
                                            componentTail += 1;
                                            component.push(neighbor);
                                        }
                                    }
                                    if (y > 0) {
                                        const neighbor = current - width;
                                        if (foregroundSeed[neighbor] && !seedVisited[neighbor]) {
                                            seedVisited[neighbor] = 1;
                                            queue[componentTail] = neighbor;
                                            componentTail += 1;
                                            component.push(neighbor);
                                        }
                                    }
                                    if (y < height - 1) {
                                        const neighbor = current + width;
                                        if (foregroundSeed[neighbor] && !seedVisited[neighbor]) {
                                            seedVisited[neighbor] = 1;
                                            queue[componentTail] = neighbor;
                                            componentTail += 1;
                                            component.push(neighbor);
                                        }
                                    }
                                }

                                if (
                                    (touchesCenter && component.length >= minCenterComponent) ||
                                    component.length >= largeAnywhereComponent
                                ) {
                                    for (let i = 0; i < component.length; i += 1) {
                                        protectMask[component[i]] = 1;
                                    }
                                }
                            }

                            // Dilate protection mask so fine edges around subjects are preserved.
                            for (let pass = 0; pass < 3; pass += 1) {
                                const nextMask = protectMask.slice();
                                for (let idx = 0; idx < pixelCount; idx += 1) {
                                    if (!protectMask[idx]) {
                                        continue;
                                    }
                                    const x = idx % width;
                                    const y = Math.floor(idx / width);
                                    for (let oy = -1; oy <= 1; oy += 1) {
                                        for (let ox = -1; ox <= 1; ox += 1) {
                                            const nx = x + ox;
                                            const ny = y + oy;
                                            if (nx < 0 || ny < 0 || nx >= width || ny >= height) {
                                                continue;
                                            }
                                            nextMask[ny * width + nx] = 1;
                                        }
                                    }
                                }
                                protectMask.set(nextMask);
                            }

                            // Recover tiny cutouts inside protected subjects (common on white shirts/highlights).
                            for (let pass = 0; pass < 2; pass += 1) {
                                const nextMask = protectMask.slice();
                                for (let idx = 0; idx < pixelCount; idx += 1) {
                                    if (protectMask[idx] || !bgMask[idx]) {
                                        continue;
                                    }
                                    const offset = getOffset(idx);
                                    if (pixels[offset + 3] < 10) {
                                        continue;
                                    }
                                    let protectedNeighbors = 0;
                                    const x = idx % width;
                                    const y = Math.floor(idx / width);
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
                                            if (protectMask[ny * width + nx]) {
                                                protectedNeighbors += 1;
                                            }
                                        }
                                    }
                                    const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                    if (protectedNeighbors >= 5 && distance <= softTolerance + 18) {
                                        nextMask[idx] = 1;
                                    }
                                }
                                protectMask.set(nextMask);
                            }

                            // Extra portrait-safe recovery:
                            // if pixels are inside/near the protected subject bounds and mostly surrounded by
                            // protected or non-background pixels, keep them to avoid cutting face/hair/clothing.
                            const subjectBoundsArea = subjectBounds.has
                                ? (subjectBounds.maxX - subjectBounds.minX + 1) * (subjectBounds.maxY - subjectBounds.minY + 1)
                                : 0;
                            const subjectBoundsRatio = pixelCount ? (subjectBoundsArea / pixelCount) : 0;
                            const canRunPortraitRecovery = subjectBounds.has && subjectBoundsRatio <= 0.82;

                            if (canRunPortraitRecovery) {
                                const interiorPadding = Math.max(4, Math.round(Math.min(width, height) * 0.015));
                                const isInsideExpandedSubject = (x, y) => (
                                    x >= subjectBounds.minX - interiorPadding &&
                                    x <= subjectBounds.maxX + interiorPadding &&
                                    y >= subjectBounds.minY - interiorPadding &&
                                    y <= subjectBounds.maxY + interiorPadding
                                );

                                for (let pass = 0; pass < 3; pass += 1) {
                                    const nextMask = protectMask.slice();
                                    for (let idx = 0; idx < pixelCount; idx += 1) {
                                        if (protectMask[idx] || !bgMask[idx]) {
                                            continue;
                                        }
                                        const offset = getOffset(idx);
                                        if (pixels[offset + 3] < 16) {
                                            continue;
                                        }

                                        const x = idx % width;
                                        const y = Math.floor(idx / width);
                                        if (!isInsideExpandedSubject(x, y)) {
                                            continue;
                                        }

                                        let protectedNeighbors = 0;
                                        let nonBgNeighbors = 0;
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
                                                const nIdx = ny * width + nx;
                                                if (protectMask[nIdx]) {
                                                    protectedNeighbors += 1;
                                                }
                                                if (!bgMask[nIdx]) {
                                                    nonBgNeighbors += 1;
                                                }
                                            }
                                        }

                                        const r = pixels[offset];
                                        const g = pixels[offset + 1];
                                        const b = pixels[offset + 2];
                                        const colorSpread = Math.max(r, g, b) - Math.min(r, g, b);
                                        const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                        const strongInterior = protectedNeighbors >= 5 || nonBgNeighbors >= 7;
                                        const closeToSubject = protectedNeighbors >= 3 && nonBgNeighbors >= 5;
                                        const likelyForeground = colorSpread >= 12 || distance >= hardTolerance * 0.72;

                                        if (strongInterior || (closeToSubject && likelyForeground)) {
                                            nextMask[idx] = 1;
                                        }
                                    }
                                    protectMask.set(nextMask);
                                }
                            }

                            // Fill enclosed holes inside the subject region so portraits do not break apart.
                            if (canRunPortraitRecovery) {
                                const holePadding = Math.max(10, Math.round(Math.min(width, height) * 0.04));
                                const regionMinX = clamp(subjectBounds.minX - holePadding, 0, width - 1);
                                const regionMaxX = clamp(subjectBounds.maxX + holePadding, 0, width - 1);
                                const regionMinY = clamp(subjectBounds.minY - holePadding, 0, height - 1);
                                const regionMaxY = clamp(subjectBounds.maxY + holePadding, 0, height - 1);
                                const exteriorBg = new Uint8Array(pixelCount);
                                const holeVisited = new Uint8Array(pixelCount);
                                const subjectArea = Math.max(1, (subjectBounds.maxX - subjectBounds.minX + 1) * (subjectBounds.maxY - subjectBounds.minY + 1));
                                const maxHoleArea = Math.max(120, Math.min(Math.round(subjectArea * 0.22), Math.round(pixelCount * 0.085)));

                                let head = 0;
                                let tail = 0;
                                const enqueueExterior = (idx) => {
                                    if (exteriorBg[idx] || protectMask[idx] || !bgMask[idx]) {
                                        return;
                                    }
                                    exteriorBg[idx] = 1;
                                    queue[tail] = idx;
                                    tail += 1;
                                };
                                const inRegion = (x, y) => (
                                    x >= regionMinX && x <= regionMaxX && y >= regionMinY && y <= regionMaxY
                                );

                                for (let x = regionMinX; x <= regionMaxX; x += 1) {
                                    enqueueExterior((regionMinY * width) + x);
                                    enqueueExterior((regionMaxY * width) + x);
                                }
                                for (let y = regionMinY + 1; y < regionMaxY; y += 1) {
                                    enqueueExterior((y * width) + regionMinX);
                                    enqueueExterior((y * width) + regionMaxX);
                                }

                                while (head < tail) {
                                    const idx = queue[head];
                                    head += 1;
                                    const x = idx % width;
                                    const y = Math.floor(idx / width);
                                    if (x > regionMinX) {
                                        const n = idx - 1;
                                        if (!exteriorBg[n] && !protectMask[n] && bgMask[n]) {
                                            exteriorBg[n] = 1;
                                            queue[tail] = n;
                                            tail += 1;
                                        }
                                    }
                                    if (x < regionMaxX) {
                                        const n = idx + 1;
                                        if (!exteriorBg[n] && !protectMask[n] && bgMask[n]) {
                                            exteriorBg[n] = 1;
                                            queue[tail] = n;
                                            tail += 1;
                                        }
                                    }
                                    if (y > regionMinY) {
                                        const n = idx - width;
                                        if (!exteriorBg[n] && !protectMask[n] && bgMask[n]) {
                                            exteriorBg[n] = 1;
                                            queue[tail] = n;
                                            tail += 1;
                                        }
                                    }
                                    if (y < regionMaxY) {
                                        const n = idx + width;
                                        if (!exteriorBg[n] && !protectMask[n] && bgMask[n]) {
                                            exteriorBg[n] = 1;
                                            queue[tail] = n;
                                            tail += 1;
                                        }
                                    }
                                }

                                for (let y = regionMinY; y <= regionMaxY; y += 1) {
                                    for (let x = regionMinX; x <= regionMaxX; x += 1) {
                                        const idx = (y * width) + x;
                                        if (protectMask[idx] || !bgMask[idx] || exteriorBg[idx] || holeVisited[idx]) {
                                            continue;
                                        }

                                        head = 0;
                                        tail = 0;
                                        queue[tail] = idx;
                                        tail += 1;
                                        holeVisited[idx] = 1;
                                        const component = [idx];
                                        let touchesSubject = 0;

                                        while (head < tail) {
                                            const current = queue[head];
                                            head += 1;
                                            const cx = current % width;
                                            const cy = Math.floor(current / width);
                                            const neighbors = [];
                                            if (cx > regionMinX) neighbors.push(current - 1);
                                            if (cx < regionMaxX) neighbors.push(current + 1);
                                            if (cy > regionMinY) neighbors.push(current - width);
                                            if (cy < regionMaxY) neighbors.push(current + width);

                                            for (let i = 0; i < neighbors.length; i += 1) {
                                                const neighbor = neighbors[i];
                                                if (protectMask[neighbor]) {
                                                    touchesSubject += 1;
                                                    continue;
                                                }
                                                if (holeVisited[neighbor] || exteriorBg[neighbor] || !bgMask[neighbor]) {
                                                    continue;
                                                }
                                                const nx = neighbor % width;
                                                const ny = Math.floor(neighbor / width);
                                                if (!inRegion(nx, ny)) {
                                                    continue;
                                                }
                                                holeVisited[neighbor] = 1;
                                                queue[tail] = neighbor;
                                                tail += 1;
                                                component.push(neighbor);
                                            }
                                        }

                                        if (
                                            component.length <= maxHoleArea &&
                                            touchesSubject >= Math.max(8, Math.round(component.length * 0.04))
                                        ) {
                                            for (let i = 0; i < component.length; i += 1) {
                                                protectMask[component[i]] = 1;
                                            }
                                        }
                                    }
                                }
                            }

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (protectMask[idx]) {
                                    bgMask[idx] = 0;
                                }
                            }
                            const subjectBoundsPadding = Math.max(8, Math.round(Math.min(width, height) * 0.02));
                            const isInsideSubjectBounds = (idx, padding = subjectBoundsPadding) => {
                                if (!subjectBounds.has) {
                                    return false;
                                }
                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                return (
                                    x >= subjectBounds.minX - padding &&
                                    x <= subjectBounds.maxX + padding &&
                                    y >= subjectBounds.minY - padding &&
                                    y <= subjectBounds.maxY + padding
                                );
                            };

                            // Clear small leftover background islands that often remain around text/wording shapes.
                            const checked = new Uint8Array(pixelCount);
                            const islandLimit = Math.max(14, Math.min(2400, Math.round(pixelCount * 0.0012)));

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (protectMask[idx] || bgMask[idx] || checked[idx] || !isBackgroundLike(idx, hardTolerance * 0.92)) {
                                    continue;
                                }
                                if (isInsideSubjectBounds(idx)) {
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
                                        if (protectMask[neighbor] || bgMask[neighbor] || checked[neighbor]) {
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

                            // Secondary pass: remove large flat-color panels (for example bold red backdrops)
                            // that may not be edge-connected but are still background.
                            let bgPixelCount = 0;
                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                if (bgMask[idx]) {
                                    bgPixelCount += 1;
                                }
                            }
                            const bgRatio = pixelCount ? (bgPixelCount / pixelCount) : 0;

                            if (bgRatio < 0.58) {
                                const vividBuckets = new Map();
                                for (let idx = 0; idx < pixelCount; idx += 1) {
                                    if (protectMask[idx] || bgMask[idx]) {
                                        continue;
                                    }
                                    const offset = getOffset(idx);
                                    if (pixels[offset + 3] < 16) {
                                        continue;
                                    }
                                    const r = pixels[offset];
                                    const g = pixels[offset + 1];
                                    const b = pixels[offset + 2];
                                    const spread = Math.max(r, g, b) - Math.min(r, g, b);
                                    if (spread < 30) {
                                        continue;
                                    }

                                    const key = `${r >> 4}:${g >> 4}:${b >> 4}`;
                                    const bucket = vividBuckets.get(key) || { count: 0, sumR: 0, sumG: 0, sumB: 0 };
                                    bucket.count += 1;
                                    bucket.sumR += r;
                                    bucket.sumG += g;
                                    bucket.sumB += b;
                                    vividBuckets.set(key, bucket);
                                }

                                let dominantVivid = null;
                                vividBuckets.forEach((bucket) => {
                                    if (!dominantVivid || bucket.count > dominantVivid.count) {
                                        dominantVivid = bucket;
                                    }
                                });

                                const vividThreshold = Math.max(1200, Math.round(pixelCount * 0.03));
                                if (dominantVivid && dominantVivid.count >= vividThreshold) {
                                    const keyR = dominantVivid.sumR / dominantVivid.count;
                                    const keyG = dominantVivid.sumG / dominantVivid.count;
                                    const keyB = dominantVivid.sumB / dominantVivid.count;
                                    const keyTolerance = clamp((hardTolerance * 0.58) + 18, 22, 54);
                                    const largePanelMin = Math.max(1400, Math.round(pixelCount * 0.032));
                                    const visitedPanels = new Uint8Array(pixelCount);
                                    const isNearKeyColor = (idx) => colorDistanceToBg(idx, keyR, keyG, keyB) <= keyTolerance;

                                    for (let idx = 0; idx < pixelCount; idx += 1) {
                                        if (visitedPanels[idx] || protectMask[idx] || bgMask[idx] || !isNearKeyColor(idx)) {
                                            continue;
                                        }
                                        const offset = getOffset(idx);
                                        if (pixels[offset + 3] < 16) {
                                            continue;
                                        }

                                        let componentHead = 0;
                                        let componentTail = 0;
                                        queue[componentTail] = idx;
                                        componentTail += 1;
                                        visitedPanels[idx] = 1;
                                        const component = [idx];
                                        let minR = 255;
                                        let minG = 255;
                                        let minB = 255;
                                        let maxR = 0;
                                        let maxG = 0;
                                        let maxB = 0;

                                        while (componentHead < componentTail) {
                                            const current = queue[componentHead];
                                            componentHead += 1;
                                            const off = getOffset(current);
                                            const r = pixels[off];
                                            const g = pixels[off + 1];
                                            const b = pixels[off + 2];
                                            minR = Math.min(minR, r);
                                            minG = Math.min(minG, g);
                                            minB = Math.min(minB, b);
                                            maxR = Math.max(maxR, r);
                                            maxG = Math.max(maxG, g);
                                            maxB = Math.max(maxB, b);

                                            const x = current % width;
                                            const y = Math.floor(current / width);
                                            const neighbors = [];
                                            if (x > 0) neighbors.push(current - 1);
                                            if (x < width - 1) neighbors.push(current + 1);
                                            if (y > 0) neighbors.push(current - width);
                                            if (y < height - 1) neighbors.push(current + width);

                                            for (let n = 0; n < neighbors.length; n += 1) {
                                                const neighbor = neighbors[n];
                                                if (visitedPanels[neighbor] || protectMask[neighbor] || bgMask[neighbor] || !isNearKeyColor(neighbor)) {
                                                    continue;
                                                }
                                                const no = getOffset(neighbor);
                                                if (pixels[no + 3] < 16) {
                                                    continue;
                                                }
                                                visitedPanels[neighbor] = 1;
                                                queue[componentTail] = neighbor;
                                                componentTail += 1;
                                                component.push(neighbor);
                                            }
                                        }

                                        const flatRange = Math.max(maxR - minR, maxG - minG, maxB - minB);
                                        if (component.length >= largePanelMin && flatRange <= 92) {
                                            for (let i = 0; i < component.length; i += 1) {
                                                bgMask[component[i]] = 1;
                                            }
                                        }
                                    }
                                }
                            }

                            // Safety fallback: avoid "nothing removed" outputs by rebuilding a balanced
                            // background mask when the selected background area is unrealistically small.
                            const finalBgRatio = estimateBackgroundRatio(bgMask);
                            if (finalBgRatio < 0.045) {
                                const retryHardTolerance = clamp(hardTolerance * 0.95, 20, 88);
                                const retrySoftTolerance = clamp(softTolerance + 8, 44, 138);
                                const retryMask = fillBackgroundMask(retryHardTolerance, retrySoftTolerance, false);
                                const retryBgRatio = estimateBackgroundRatio(retryMask);
                                const retryBorderRatio = estimateBorderBackgroundRatio(retryMask);

                                if (retryBgRatio >= 0.12 && retryBorderRatio >= 0.62) {
                                    bgMask = retryMask;
                                    for (let idx = 0; idx < pixelCount; idx += 1) {
                                        if (subjectMask[idx]) {
                                            bgMask[idx] = 0;
                                        }
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
                            const estimateLocalContrast = (idx) => {
                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                const offset = getOffset(idx);
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                let total = 0;
                                let samples = 0;
                                if (x > 0) {
                                    const n = getOffset(idx - 1);
                                    total += (Math.abs(r - pixels[n]) + Math.abs(g - pixels[n + 1]) + Math.abs(b - pixels[n + 2])) / 3;
                                    samples += 1;
                                }
                                if (x < width - 1) {
                                    const n = getOffset(idx + 1);
                                    total += (Math.abs(r - pixels[n]) + Math.abs(g - pixels[n + 1]) + Math.abs(b - pixels[n + 2])) / 3;
                                    samples += 1;
                                }
                                if (y > 0) {
                                    const n = getOffset(idx - width);
                                    total += (Math.abs(r - pixels[n]) + Math.abs(g - pixels[n + 1]) + Math.abs(b - pixels[n + 2])) / 3;
                                    samples += 1;
                                }
                                if (y < height - 1) {
                                    const n = getOffset(idx + width);
                                    total += (Math.abs(r - pixels[n]) + Math.abs(g - pixels[n + 1]) + Math.abs(b - pixels[n + 2])) / 3;
                                    samples += 1;
                                }
                                return samples ? total / samples : 0;
                            };
                            const hasProtectedNeighbor = (x, y) => {
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
                                        if (protectMask[ny * width + nx] || subjectMask[ny * width + nx]) {
                                            return true;
                                        }
                                    }
                                }
                                return false;
                            };

                            for (let idx = 0; idx < pixelCount; idx += 1) {
                                const offset = getOffset(idx);
                                let alpha = pixels[offset + 3];
                                if (protectMask[idx]) {
                                    continue;
                                }
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
                                const nearSubject = hasProtectedNeighbor(x, y) || isInsideSubjectBounds(idx, subjectBoundsPadding + 4);
                                const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                const r = pixels[offset];
                                const g = pixels[offset + 1];
                                const b = pixels[offset + 2];
                                const brightness = (r + g + b) / 3;
                                const colorSpread = Math.max(r, g, b) - Math.min(r, g, b);
                                const localContrast = estimateLocalContrast(idx);
                                const isBlurryBackgroundPixel = !nearSubject
                                    && bgNeighbors >= 3
                                    && distance <= softTolerance + 22
                                    && colorSpread <= 22
                                    && localContrast <= 18;

                                if (isBlurryBackgroundPixel) {
                                    pixels[offset + 3] = 0;
                                    continue;
                                }

                                if (bgNeighbors > 0 && distance <= softTolerance + 8) {
                                    const softness = clamp((softTolerance - distance) / Math.max(1, softTolerance - hardTolerance), 0, 1);
                                    const edgeFactor = nearSubject
                                        ? (bgNeighbors >= 5 ? 0.7 : bgNeighbors >= 3 ? 0.54 : 0.38)
                                        : (bgNeighbors >= 5 ? 0.92 : bgNeighbors >= 3 ? 0.76 : 0.6);
                                    alpha = Math.round(alpha * (1 - softness * edgeFactor));
                                }

                                // Remove dark outline/stroke on cut edges by detecting fringe pixels that are
                                // darker than nearby kept subject pixels.
                                if (nearSubject && bgNeighbors >= 2 && distance <= softTolerance + 24) {
                                    let fgNeighbors = 0;
                                    let fgBrightnessTotal = 0;
                                    let fgRTotal = 0;
                                    let fgGTotal = 0;
                                    let fgBTotal = 0;
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
                                            const nIdx = ny * width + nx;
                                            if (bgMask[nIdx]) {
                                                continue;
                                            }
                                            const no = getOffset(nIdx);
                                            if (pixels[no + 3] <= 0) {
                                                continue;
                                            }
                                            const nr = pixels[no];
                                            const ng = pixels[no + 1];
                                            const nb = pixels[no + 2];
                                            fgNeighbors += 1;
                                            fgRTotal += nr;
                                            fgGTotal += ng;
                                            fgBTotal += nb;
                                            fgBrightnessTotal += (nr + ng + nb) / 3;
                                        }
                                    }

                                    if (fgNeighbors >= 3) {
                                        const avgFgBrightness = fgBrightnessTotal / fgNeighbors;
                                        const isDarkFringe = colorSpread <= 52 && (avgFgBrightness - brightness) >= 18;
                                        if (isDarkFringe) {
                                            const fringeStrength = clamp((avgFgBrightness - brightness) / 110, 0, 1);
                                            const fringeFade = bgNeighbors >= 5 ? 0.34 : (bgNeighbors >= 3 ? 0.26 : 0.2);
                                            alpha = Math.round(alpha * (1 - (fringeStrength * fringeFade)));

                                            const avgFgR = fgRTotal / fgNeighbors;
                                            const avgFgG = fgGTotal / fgNeighbors;
                                            const avgFgB = fgBTotal / fgNeighbors;
                                            const colorBlend = clamp(0.18 + (fringeStrength * 0.38), 0.18, 0.56);
                                            pixels[offset] = Math.round((pixels[offset] * (1 - colorBlend)) + (avgFgR * colorBlend));
                                            pixels[offset + 1] = Math.round((pixels[offset + 1] * (1 - colorBlend)) + (avgFgG * colorBlend));
                                            pixels[offset + 2] = Math.round((pixels[offset + 2] * (1 - colorBlend)) + (avgFgB * colorBlend));
                                        }
                                    }
                                }

                                // Remove bright/white halo strokes that can remain from pre-cutout source images.
                                if (bgNeighbors >= 2) {
                                    let fgNeighbors = 0;
                                    let fgRTotal = 0;
                                    let fgGTotal = 0;
                                    let fgBTotal = 0;
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
                                            const nIdx = ny * width + nx;
                                            if (bgMask[nIdx]) {
                                                continue;
                                            }
                                            const no = getOffset(nIdx);
                                            if (pixels[no + 3] <= 0) {
                                                continue;
                                            }
                                            fgNeighbors += 1;
                                            fgRTotal += pixels[no];
                                            fgGTotal += pixels[no + 1];
                                            fgBTotal += pixels[no + 2];
                                        }
                                    }

                                    if (fgNeighbors >= 2) {
                                        const avgFgR = fgRTotal / fgNeighbors;
                                        const avgFgG = fgGTotal / fgNeighbors;
                                        const avgFgB = fgBTotal / fgNeighbors;
                                        const avgFgBrightness = (avgFgR + avgFgG + avgFgB) / 3;
                                        const isNearWhite = r >= 224 && g >= 224 && b >= 224;
                                        const isNeutralLight = brightness >= 210 && colorSpread <= 22;
                                        const edgeLighterThanInterior = (brightness - avgFgBrightness) >= 14 && colorSpread <= 42;
                                        const whiteHaloPixel = isNearWhite || isNeutralLight || edgeLighterThanInterior;

                                        if (whiteHaloPixel) {
                                            const haloStrength = clamp((brightness - avgFgBrightness + 26) / 120, 0, 1);
                                            const blend = clamp(0.24 + (haloStrength * 0.5), 0.24, 0.7);
                                            pixels[offset] = Math.round((pixels[offset] * (1 - blend)) + (avgFgR * blend));
                                            pixels[offset + 1] = Math.round((pixels[offset + 1] * (1 - blend)) + (avgFgG * blend));
                                            pixels[offset + 2] = Math.round((pixels[offset + 2] * (1 - blend)) + (avgFgB * blend));

                                            const thinHalo = bgNeighbors >= 5 && fgNeighbors <= 3;
                                            if (thinHalo) {
                                                alpha = Math.round(alpha * (1 - (0.45 + (haloStrength * 0.35))));
                                            } else {
                                                alpha = Math.round(alpha * (1 - (0.16 + (haloStrength * 0.2))));
                                            }
                                        }
                                    }
                                }

                                if (!nearSubject && distance <= hardTolerance * 0.82 && bgNeighbors >= 4) {
                                    alpha = Math.min(alpha, 20);
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

                            const defringeEdgeStrokes = () => {
                                const snapshot = new Uint8ClampedArray(pixels);
                                const getSnapshotOffset = (idx) => idx * 4;
                                const isCutPixel = (idx) => bgMask[idx] || snapshot[getSnapshotOffset(idx) + 3] < 16;

                                for (let idx = 0; idx < pixelCount; idx += 1) {
                                    const offset = getOffset(idx);
                                    const alpha = snapshot[offset + 3];
                                    if (alpha < 16 || bgMask[idx]) {
                                        continue;
                                    }

                                    const x = idx % width;
                                    const y = Math.floor(idx / width);
                                    let cutNeighbors = 0;
                                    let interiorCount = 0;
                                    let interiorR = 0;
                                    let interiorG = 0;
                                    let interiorB = 0;
                                    let interiorBrightness = 0;

                                    for (let oy = -2; oy <= 2; oy += 1) {
                                        for (let ox = -2; ox <= 2; ox += 1) {
                                            if (ox === 0 && oy === 0) {
                                                continue;
                                            }

                                            const nx = x + ox;
                                            const ny = y + oy;
                                            if (nx < 0 || ny < 0 || nx >= width || ny >= height) {
                                                continue;
                                            }

                                            const neighbor = (ny * width) + nx;
                                            const neighborOffset = getSnapshotOffset(neighbor);
                                            const neighborAlpha = snapshot[neighborOffset + 3];

                                            if (Math.abs(ox) <= 1 && Math.abs(oy) <= 1 && isCutPixel(neighbor)) {
                                                cutNeighbors += 1;
                                            }

                                            if (!bgMask[neighbor] && neighborAlpha >= 96) {
                                                const neighborDistance = colorDistanceToBg(neighbor, bgR, bgG, bgB);
                                                if (neighborDistance > hardTolerance + 10) {
                                                    const nr = snapshot[neighborOffset];
                                                    const ng = snapshot[neighborOffset + 1];
                                                    const nb = snapshot[neighborOffset + 2];
                                                    interiorCount += 1;
                                                    interiorR += nr;
                                                    interiorG += ng;
                                                    interiorB += nb;
                                                    interiorBrightness += (nr + ng + nb) / 3;
                                                }
                                            }
                                        }
                                    }

                                    if (cutNeighbors < 2 || interiorCount < 2) {
                                        continue;
                                    }

                                    const r = snapshot[offset];
                                    const g = snapshot[offset + 1];
                                    const b = snapshot[offset + 2];
                                    const brightness = (r + g + b) / 3;
                                    const spread = Math.max(r, g, b) - Math.min(r, g, b);
                                    const avgInteriorR = interiorR / interiorCount;
                                    const avgInteriorG = interiorG / interiorCount;
                                    const avgInteriorB = interiorB / interiorCount;
                                    const avgInteriorBrightness = interiorBrightness / interiorCount;
                                    const distance = colorDistanceToBg(idx, bgR, bgG, bgB);
                                    const matchesOldBackground = distance <= softTolerance + 36;
                                    const looksLikeDarkStroke = brightness + 18 < avgInteriorBrightness && spread <= 76;

                                    if (!matchesOldBackground && !looksLikeDarkStroke) {
                                        continue;
                                    }

                                    const strokeStrength = clamp(
                                        Math.max((softTolerance + 36 - distance) / Math.max(1, softTolerance + 36), (avgInteriorBrightness - brightness) / 120),
                                        0,
                                        1
                                    );
                                    const blend = clamp(0.34 + strokeStrength * 0.5, 0.34, 0.84);
                                    pixels[offset] = Math.round((r * (1 - blend)) + (avgInteriorR * blend));
                                    pixels[offset + 1] = Math.round((g * (1 - blend)) + (avgInteriorG * blend));
                                    pixels[offset + 2] = Math.round((b * (1 - blend)) + (avgInteriorB * blend));

                                    const fade = cutNeighbors >= 5
                                        ? clamp(0.38 + strokeStrength * 0.42, 0.38, 0.8)
                                        : clamp(0.18 + strokeStrength * 0.3, 0.18, 0.48);
                                    const nextAlpha = Math.round(alpha * (1 - fade));
                                    pixels[offset + 3] = nextAlpha < 14 ? 0 : nextAlpha;
                                }
                            };

                            defringeEdgeStrokes();
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
