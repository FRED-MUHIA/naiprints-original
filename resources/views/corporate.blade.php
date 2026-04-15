<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $faviconUrl = $globalHeader['favicon_url'] ?? '';
            $faviconUrl = $faviconUrl ?: '/images/logo.svg';
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <title>Corporate Merch | Nai Prints</title>
        <meta name="description" content="Corporate merch and uniform printing for teams. Dedicated account managers, fast turnaround, and consistent branding.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-sand text-ink">
        <div class="min-h-screen">
            @include('partials.site-header', ['globalHeader' => $globalHeader ?? []])

            <main class="mx-auto max-w-6xl px-4 py-12">
                @php
                    $sections = $pageContent['sections'] ?? [];
                    $section = collect($sections)->firstWhere('type', 'corporate_page');
                    $defaults = \App\Support\PageDefaults::corporate()['sections'][0]['data'] ?? [];
                    $data = $section['data'] ?? [];

                    $hero = $data['hero'] ?? ($defaults['hero'] ?? []);
                    $heroStats = $hero['stats'] ?? ($defaults['hero']['stats'] ?? []);
                    $heroImage = $hero['image'] ?? ($defaults['hero']['image'] ?? '/images/corporate-hero.png');
                    $heroAlt = $hero['image_alt'] ?? ($defaults['hero']['image_alt'] ?? 'Corporate merch preview');
                    $heroImagePath = $heroImage ? public_path(ltrim($heroImage, '/')) : null;
                    $hasHeroImage = $heroImagePath && file_exists($heroImagePath);

                    $highlights = $data['highlights'] ?? ($defaults['highlights'] ?? []);
                    $process = $data['process'] ?? ($defaults['process'] ?? []);
                    $processSteps = $process['steps'] ?? ($defaults['process']['steps'] ?? []);
                    $logos = $data['logos'] ?? ($defaults['logos'] ?? []);
                    $logoItems = $logos['items'] ?? ($defaults['logos']['items'] ?? []);
                    $resolveContactCtaUrl = static function ($label, $url = '#') {
                        $normalizedLabel = strtolower(trim((string) $label));
                        $resolvedUrl = trim((string) $url);
                        if (
                            in_array($normalizedLabel, ['request a quote', 'start your order'], true)
                            && ($resolvedUrl === '' || $resolvedUrl === '#' || $resolvedUrl === '/shop')
                        ) {
                            return route('contact.show');
                        }

                        return $resolvedUrl !== '' ? $resolvedUrl : '#';
                    };

                    $heroPrimaryLabel = trim((string) ($hero['primary']['label'] ?? ''));
                    $heroPrimaryUrl = $resolveContactCtaUrl($heroPrimaryLabel, $hero['primary']['url'] ?? '#');
                    $cta = $data['cta'] ?? ($defaults['cta'] ?? []);
                    $ctaPrimaryLabel = trim((string) ($cta['primary']['label'] ?? ''));
                    $ctaPrimaryUrl = $resolveContactCtaUrl($ctaPrimaryLabel, $cta['primary']['url'] ?? '#');
                @endphp

                <section class="corporate-hero-grid">
                    @if ($hasHeroImage)
                        <div class="corporate-hero-media category-card">
                            <img src="{{ $heroImage }}" alt="{{ $heroAlt }}" class="corporate-hero-image">
                        </div>
                    @endif
                    <div class="hero-card corporate-hero-copy">
                        @if (!empty($hero['kicker']) || !empty($hero['kicker_note']))
                            <div class="corporate-update">
                                @if (!empty($hero['kicker']))
                                    <span class="pill">{{ $hero['kicker'] }}</span>
                                @endif
                                @if (!empty($hero['kicker_note']))
                                    <span class="corporate-update-text">{{ $hero['kicker_note'] }}</span>
                                @endif
                                <span class="corporate-update-arrow">&rarr;</span>
                            </div>
                        @endif
                        @if (!empty($hero['title']))
                            <h1 class="mt-6 text-4xl font-semibold leading-tight text-white md:text-5xl">
                                {{ $hero['title'] }}
                            </h1>
                        @endif
                        @if (!empty($hero['body']))
                            <p class="mt-4 text-sm text-white/80 md:text-base">
                                {{ $hero['body'] }}
                            </p>
                        @endif
                        <div class="corporate-hero-actions">
                            @if ($heroPrimaryLabel !== '')
                                <a href="{{ $heroPrimaryUrl }}" class="btn-primary">{{ $heroPrimaryLabel }}</a>
                            @endif
                            @if (!empty($hero['secondary']['label']))
                                <a href="{{ $hero['secondary']['url'] ?? '#' }}" class="btn-secondary">{{ $hero['secondary']['label'] }}</a>
                            @endif
                        </div>
                        @if (!empty($heroStats))
                            <div class="corporate-hero-stats">
                                @foreach ($heroStats as $stat)
                                    <div>
                                        <p class="corporate-hero-stat-value">{{ $stat['value'] ?? '' }}</p>
                                        <p class="corporate-hero-stat-label">{{ $stat['label'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                @if (!empty($highlights))
                    <section class="mt-12 grid gap-6 md:grid-cols-3">
                        @foreach ($highlights as $item)
                            <div class="category-card corporate-card">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-3 text-sm text-slate-600">{{ $item['body'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </section>
                @endif

                <section class="mt-12">
                    @if (!empty($process['kicker']))
                        <p class="section-kicker">{{ $process['kicker'] }}</p>
                    @endif
                    @if (!empty($process['title']))
                        <h2 class="section-title">{{ $process['title'] }}</h2>
                    @endif
                    @if (!empty($processSteps))
                        <div class="mt-6 grid gap-6 md:grid-cols-2">
                            @foreach ($processSteps as $index => $step)
                                <div class="corporate-step">
                                    <span class="corporate-step-index">Step {{ $index + 1 }}</span>
                                    <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $step['title'] ?? '' }}</h3>
                                    <p class="mt-2 text-sm text-slate-600">{{ $step['body'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if (!empty($logoItems))
                    <section class="mt-12 corporate-logo-strip">
                        @if (!empty($logos['kicker']))
                            <p class="section-kicker">{{ $logos['kicker'] }}</p>
                        @endif
                        @if (!empty($logos['title']))
                            <h2 class="section-title">{{ $logos['title'] }}</h2>
                        @endif
                        <div class="corporate-logo-grid">
                            @foreach ($logoItems as $item)
                                <span class="corporate-logo-pill">{{ $item }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="corporate-form">
                    <div class="corporate-form-header">
                        <p class="section-kicker">Corporate Quote</p>
                        <h2 class="section-title">Tell us about your order</h2>
                        <p class="corporate-form-subtitle">
                            Share your requirements and we will send a detailed quote, mockup, and timeline within 24 hours.
                        </p>
                        @if (session('status'))
                            <p class="corporate-form-alert">{{ session('status') }}</p>
                        @endif
                    </div>
                    <form class="corporate-form-grid" action="{{ route('corporate.quote') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="corporate-form-block">
                            <h3>Personal Information</h3>
                            <div class="corporate-form-fields">
                                <label class="corporate-field">
                                    <span class="corporate-label">Name <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="text" name="full_name" required placeholder="Jane Doe">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Company <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="text" name="company" required placeholder="Nai Prints Ltd">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Role / Department</span>
                                    <input class="corporate-input" type="text" name="role" placeholder="Procurement, HR, Marketing">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Phone <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="tel" name="phone" required placeholder="+254 7xx xxx xxx">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Email <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="email" name="email" required placeholder="you@company.com">
                                </label>
                                <label class="corporate-field corporate-form-full">
                                    <span class="corporate-label">Delivery/Shipping Address</span>
                                    <input class="corporate-input" type="text" name="location" placeholder="Nairobi, Kenya">
                                </label>
                            </div>
                        </div>

                        <div class="corporate-form-block">
                            <h3>Product & Quantity</h3>
                            <div class="corporate-form-fields">
                                <label class="corporate-field">
                                    <span class="corporate-label">Product Type <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="text" name="product_type" required placeholder="T-shirt, Hoodie, Polo, Cap">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Expected Delivery Date</span>
                                    <input class="corporate-input" type="date" name="delivery_date">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Total Quantity <span class="corporate-required">*</span></span>
                                    <input class="corporate-input" type="number" name="total_quantity" min="1" required placeholder="120">
                                </label>
                                <div class="corporate-size-grid corporate-form-full">
                                    <label class="corporate-field">
                                        <span class="corporate-label">XS</span>
                                        <input class="corporate-input" type="number" name="size_xs" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">S</span>
                                        <input class="corporate-input" type="number" name="size_s" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">M</span>
                                        <input class="corporate-input" type="number" name="size_m" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">L</span>
                                        <input class="corporate-input" type="number" name="size_l" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">XL</span>
                                        <input class="corporate-input" type="number" name="size_xl" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">XXL</span>
                                        <input class="corporate-input" type="number" name="size_xxl" min="0" placeholder="0">
                                    </label>
                                    <label class="corporate-field">
                                        <span class="corporate-label">XXXL</span>
                                        <input class="corporate-input" type="number" name="size_xxxl" min="0" placeholder="0">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="corporate-form-block">
                            <h3>Branding Details</h3>
                            <div class="corporate-form-fields">
                                <label class="corporate-field">
                                    <span class="corporate-label">Primary Color</span>
                                    <select class="corporate-input" name="color">
                                        <option value="">Select a color</option>
                                        <option>White</option>
                                        <option>Black</option>
                                        <option>Gray</option>
                                        <option>Navy</option>
                                        <option>Red</option>
                                        <option>Custom</option>
                                    </select>
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Branding Method</span>
                                    <select class="corporate-input" name="branding">
                                        <option value="">Select a method</option>
                                        <option>Screen print</option>
                                        <option>Embroidery</option>
                                        <option>Heat transfer</option>
                                        <option>Not sure</option>
                                    </select>
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Artwork / Logo Files</span>
                                    <input class="corporate-input corporate-file" type="file" name="artwork" accept="image/*,.svg,.pdf">
                                </label>
                                <label class="corporate-field">
                                    <span class="corporate-label">Budget Range</span>
                                    <select class="corporate-input" name="budget">
                                        <option value="">Select range</option>
                                        <option>KSh 20k - 50k</option>
                                        <option>KSh 50k - 100k</option>
                                        <option>KSh 100k - 250k</option>
                                        <option>KSh 250k+</option>
                                    </select>
                                </label>
                                <label class="corporate-field corporate-form-full">
                                    <span class="corporate-label">Additional Comments</span>
                                    <textarea class="corporate-input" name="comments" rows="4" placeholder="Tell us about placements, sizing notes, or delivery instructions."></textarea>
                                </label>
                            </div>
                        </div>

                        <div class="corporate-form-actions">
                            <button type="submit" class="btn-brand">Submit request</button>
                            <p class="corporate-form-note">By submitting, you agree to be contacted about this request.</p>
                        </div>
                    </form>
                </section>

                <section class="corporate-cta">
                    @if (!empty($cta['title']))
                        <h2 class="text-3xl font-semibold text-white md:text-4xl">{{ $cta['title'] }}</h2>
                    @endif
                    @if (!empty($cta['body']))
                        <p class="mt-3 text-sm text-white/80 md:text-base">{{ $cta['body'] }}</p>
                    @endif
                    <div class="corporate-cta-actions">
                        @if ($ctaPrimaryLabel !== '')
                            <a href="{{ $ctaPrimaryUrl }}" class="btn-primary">{{ $ctaPrimaryLabel }}</a>
                        @endif
                        @if (!empty($cta['secondary']['label']))
                            <a href="{{ $cta['secondary']['url'] ?? '#' }}" class="btn-secondary">{{ $cta['secondary']['label'] }}</a>
                        @endif
                    </div>
                </section>
            </main>

            @include('partials.site-footer', ['globalFooter' => $globalFooter ?? []])
        </div>
    </body>
</html>
