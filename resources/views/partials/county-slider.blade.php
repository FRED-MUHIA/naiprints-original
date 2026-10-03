@php
    $servedCounties = [
        'Mombasa', 'Kwale', 'Kilifi', 'Tana River', 'Lamu', 'Taita Taveta',
        'Garissa', 'Wajir', 'Mandera', 'Marsabit', 'Isiolo', 'Meru',
        'Tharaka Nithi', 'Embu', 'Kitui', 'Machakos', 'Makueni', 'Nyandarua',
        'Nyeri', 'Kirinyaga', 'Murang’a', 'Kiambu', 'Turkana', 'West Pokot',
        'Samburu', 'Trans Nzoia', 'Uasin Gishu', 'Elgeyo Marakwet', 'Nandi',
        'Baringo', 'Laikipia', 'Nakuru', 'Narok', 'Kajiado', 'Kericho',
        'Bomet', 'Kakamega', 'Vihiga', 'Bungoma', 'Busia', 'Siaya',
        'Kisumu', 'Homa Bay', 'Migori', 'Kisii', 'Nyamira', 'Nairobi',
    ];
@endphp

<section class="county-slider" aria-label="Counties served by Nai Prints">
    <div class="county-slider-heading">
        <p class="section-kicker">Where we print &amp; deliver</p>
        <h2 class="section-title">Serving all 47 counties of Kenya</h2>
    </div>
    <input class="county-slider-pause" type="checkbox" id="county-slider-pause">
    <label class="county-slider-pause-label" for="county-slider-pause">Pause scrolling</label>
    <div class="county-slider-viewport" tabindex="0" aria-label="Counties served. Scroll horizontally to explore.">
        <div class="county-slider-track">
            @for ($copy = 0; $copy < 2; $copy++)
                <ul class="county-slider-group" @if ($copy > 0) aria-hidden="true" @endif>
                    @foreach ($servedCounties as $county)
                        <li class="county-slider-pill">
                            <svg class="county-slider-flag" viewBox="0 0 30 20" aria-hidden="true" focusable="false">
                                <path fill="#fff" d="M0 0h30v20H0z" />
                                <path fill="#111" d="M0 0h30v6H0z" />
                                <path fill="#bb162b" d="M0 7h30v6H0z" />
                                <path fill="#168348" d="M0 14h30v6H0z" />
                                <path stroke="#fff" stroke-width=".8" d="m12 3 6 14m0-14-6 14" />
                                <ellipse cx="15" cy="10" rx="3" ry="6.5" fill="#bb162b" stroke="#fff" stroke-width=".6" />
                                <path stroke="#fff" stroke-width=".7" d="M15 4v12" />
                            </svg>
                            <span>{{ $county }}</span>
                            <span class="county-slider-dot" aria-hidden="true"></span>
                        </li>
                    @endforeach
                </ul>
            @endfor
        </div>
    </div>
</section>
