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
    <div class="county-slider-viewport" tabindex="0" aria-label="Counties served. Scroll horizontally to explore.">
        <div class="county-slider-track">
            @for ($copy = 0; $copy < 2; $copy++)
                <ul class="county-slider-group" @if ($copy > 0) aria-hidden="true" @endif>
                    @foreach ($servedCounties as $county)
                        <li class="county-slider-pill">
                            <span>{{ $county }}</span>
                            <span class="county-slider-dot" aria-hidden="true"></span>
                        </li>
                    @endforeach
                </ul>
            @endfor
        </div>
    </div>
</section>
