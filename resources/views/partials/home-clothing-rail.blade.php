            @if (($clothingRailEnabled ?? false) && ($clothingRailProducts ?? collect())->isNotEmpty())
                @php
                    $initialRailProduct = $clothingRailProducts->first();
                    $railCount = $clothingRailProducts->count();
                @endphp
                <section class="clothing-rail" data-clothing-rail
                         data-autoplay="{{ !empty($clothingRailAutoplay) ? 'true' : 'false' }}"
                         data-appearance="{{ $clothingRailAppearance ?? 'showroom' }}"
                         data-interval="{{ (int) ($clothingRailInterval ?? 6500) }}"
                         @if (($clothingRailAppearance ?? 'showroom') === 'custom' || !empty($clothingRailBackgroundImage)) style="@if (($clothingRailAppearance ?? 'showroom') === 'custom') --rail-bg: {{ $clothingRailBackgroundColor ?? '#07090d' }}; @endif @if (!empty($clothingRailBackgroundImage)) --rail-bg-image: url('{{ $clothingRailBackgroundImage }}'); @endif" @endif
                         aria-label="Featured Nai Prints products">
                    <div class="clothing-rail-shell">
                        <div class="clothing-rail-heading">
                            <div>
                                <p class="clothing-rail-eyebrow">NAI PRINTS <span aria-hidden="true">/</span> THE COLLECTION</p>
                                <h2>{{ $clothingRailTitle ?? 'Find your next favourite.' }}</h2>
                            </div>
                            <p class="clothing-rail-note">A closer look at pieces made for your everyday.</p>
                        </div>

                        <div class="clothing-rail-stage" data-rail-stage>
                            <svg class="clothing-rail-metal" viewBox="0 0 1200 80" preserveAspectRatio="none" aria-hidden="true">
                                <defs>
                                    <linearGradient id="clothing-rail-silver" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#f4f6f8" />
                                        <stop offset="22%" stop-color="#aeb5bd" />
                                        <stop offset="48%" stop-color="#6f7882" />
                                        <stop offset="62%" stop-color="#e2e5e8" />
                                        <stop offset="100%" stop-color="#858d96" />
                                    </linearGradient>
                                </defs>
                                <path d="M2 8 Q600 65 1198 8" class="clothing-rail-wire-shadow" />
                                <path d="M2 8 Q600 65 1198 8" class="clothing-rail-wire" />
                                <path d="M4 7 Q600 64 1196 7" class="clothing-rail-wire-highlight" />
                            </svg>
                            <div class="clothing-rail-viewport" data-rail-viewport tabindex="0" aria-label="Product rail. Use the left and right arrow keys to browse.">
                                <div class="clothing-rail-track" data-rail-track>
                                    @foreach ($clothingRailProducts as $railIndex => $railProduct)
                                        <article class="clothing-rail-item @if ($railIndex === 0) is-active @endif @if (($railProduct['hanger_style'] ?? 'hook_only') === 'hook_only') is-hook-only @endif"
                                                 data-rail-item
                                                 data-index="{{ $railIndex }}"
                                                 data-name="{{ $railProduct['name'] }}"
                                                 data-category="{{ $railProduct['category'] }}"
                                                 data-price="KSh {{ $railProduct['price'] }}"
                                                 data-url="{{ $railProduct['url'] }}"
                                                 data-has-back="{{ $railProduct['has_back_image'] ? 'true' : 'false' }}"
                                                 style="--rail-overlap: {{ (int) $railProduct['image_overlap'] }}px"
                                                 aria-current="{{ $railIndex === 0 ? 'true' : 'false' }}">
                                            <span class="clothing-rail-hanger" aria-hidden="true">
                                                <svg viewBox="0 0 100 62" fill="none">
                                                    <path d="M50 1c0 8-8 10-8 17 0 5 3 8 8 8" stroke="#9ba1a7" stroke-width="2.2" stroke-linecap="round"/>
                                                    <g class="clothing-rail-hanger-arms">
                                                        <path d="M50 26 8 50M50 26l42 24" stroke="#aeb4ba" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M5 51h7m76 0h7" stroke="#c7ccd1" stroke-width="2.2" stroke-linecap="butt"/>
                                                    </g>
                                                </svg>
                                            </span>
                                            <button type="button" class="clothing-rail-garment" data-rail-select
                                                    aria-label="Select or open {{ $railProduct['name'] }}, KSh {{ $railProduct['price'] }}"
                                                    aria-pressed="{{ $railIndex === 0 ? 'true' : 'false' }}">
                                                <span class="clothing-rail-image-stage">
                                                    <img class="rail-view-side" src="{{ $railProduct['side_image'] }}" alt="{{ $railProduct['alt'] }} — side view"
                                                         loading="{{ $railIndex === 0 ? 'eager' : 'lazy' }}" data-rail-image="side"
                                                         @if ($railIndex === 0) fetchpriority="high" @endif>
                                                    <img class="rail-view-front" src="{{ $railProduct['front_image'] }}" alt="{{ $railProduct['alt'] }} — front view" loading="lazy" data-rail-image="front">
                                                    <img class="rail-view-back" src="{{ $railProduct['back_image'] }}" alt="{{ $railProduct['alt'] }} — back view" loading="lazy" data-rail-image="back">
                                                </span>
                                            </button>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                            <div class="clothing-rail-shade" aria-hidden="true"></div>
                        </div>

                        <div class="clothing-rail-details" aria-live="polite" aria-atomic="true">
                            <div class="clothing-rail-product-copy">
                                <p data-rail-category>{{ $initialRailProduct['category'] }}</p>
                                <h2 data-rail-name>{{ $initialRailProduct['name'] }}</h2>
                                <p class="clothing-rail-price" data-rail-price>KSh {{ $initialRailProduct['price'] }}</p>
                            </div>
                            <a class="clothing-rail-view" data-rail-link href="{{ $initialRailProduct['url'] }}">View product <span aria-hidden="true">↗</span></a>
                        </div>

                        <div class="clothing-rail-controls">
                            <div class="clothing-rail-arrows">
                                <button type="button" data-rail-prev aria-label="Previous product">←</button>
                                <button type="button" data-rail-next aria-label="Next product">→</button>
                            </div>
                            <div class="clothing-rail-counter"><span data-rail-current>01</span><i aria-hidden="true">/</i><span>{{ str_pad((string) $railCount, 2, '0', STR_PAD_LEFT) }}</span></div>
                            <button type="button" class="clothing-rail-play" data-rail-play aria-pressed="{{ !empty($clothingRailAutoplay) ? 'false' : 'true' }}" aria-label="Pause automatic product browsing">Pause</button>
                        </div>
                    </div>
                </section>
            @endif

