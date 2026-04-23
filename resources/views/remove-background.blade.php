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
                        <p class="rb-reviews-sub">Real user feedback</p>
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
                            const centerTolerance = clamp(hardTolerance + 12, hardTolerance + 4, Math.max(hardTolerance + 5, softTolerance - 8));
                            const coreTolerance = clamp(hardTolerance + 6, hardTolerance + 2, centerTolerance);

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
                                const x = idx % width;
                                const y = Math.floor(idx / width);
                                const tolerance = isInCoreBand(x, y)
                                    ? coreTolerance
                                    : (isInCenterBand(x, y) ? centerTolerance : softTolerance);
                                if (!isBackgroundLike(idx, tolerance)) {
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
                                        ? (bgNeighbors >= 5 ? 0.58 : bgNeighbors >= 3 ? 0.44 : 0.3)
                                        : (bgNeighbors >= 5 ? 0.92 : bgNeighbors >= 3 ? 0.76 : 0.6);
                                    alpha = Math.round(alpha * (1 - softness * edgeFactor));
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
