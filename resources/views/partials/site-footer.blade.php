@php
    $footerDefaults = \App\Support\PageDefaults::footer();
    $footer = $globalFooter ?? [];
    if (!is_array($footer)) {
        $footer = [];
    }
    $footer = array_replace($footerDefaults, $footer);
    $columns = $footer['columns'] ?? [];
    $ethicalPolicyUrl = route('ethical-policies');
    $hasEthicalPolicyLink = false;
    foreach ($columns as $column) {
        foreach (($column['links'] ?? []) as $link) {
            if (($link['url'] ?? '') === $ethicalPolicyUrl || strcasecmp((string) ($link['label'] ?? ''), 'Ethical Policies') === 0) {
                $hasEthicalPolicyLink = true;
                break 2;
            }
        }
    }
    if (!$hasEthicalPolicyLink) {
        $companyColumnIndex = null;
        foreach ($columns as $index => $column) {
            if (strcasecmp((string) ($column['title'] ?? ''), 'Company') === 0) {
                $companyColumnIndex = $index;
                break;
            }
        }
        if ($companyColumnIndex === null) {
            $columns[] = ['title' => 'Company', 'links' => []];
            $companyColumnIndex = array_key_last($columns);
        }
        $columns[$companyColumnIndex]['links'][] = ['label' => 'Ethical Policies', 'url' => $ethicalPolicyUrl];
    }
    $hours = $footer['hours'] ?? [];
    $logoUrl = !empty($footer['logo_url']) ? $footer['logo_url'] : '/images/logo.svg';
@endphp

<footer class="border-t border-white/10 bg-[#0b1f4d] text-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-4">
        <div>
            <img src="{{ $logoUrl }}" alt="{{ $footer['brand'] ?? 'Nai Prints' }} logo" class="h-10 w-10 rounded-full object-cover">
            <p class="mt-2 text-sm text-white">{{ $footer['description'] ?? '' }}</p>
            @if (!empty($hours))
                <div class="mt-4 flex gap-3 text-sm text-white/90">
                    @foreach ($hours as $value)
                        <span>{{ $value }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        @foreach ($columns as $column)
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-white">{{ $column['title'] ?? '' }}</p>
                <ul class="mt-3 space-y-2 text-sm text-white">
                    @foreach (($column['links'] ?? []) as $link)
                        <li>
                            <a class="text-white hover:text-brand-sun" href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="border-t border-white/10 py-4 text-center text-xs text-white/90">
        <span>{{ $footer['copyright'] ?? '' }}</span>
        <span class="mx-2 text-white/40">|</span>
        <a class="text-white hover:text-brand-sun" href="{{ $ethicalPolicyUrl }}">Ethical Policies</a>
    </div>
</footer>
<div class="toast" data-cart-toast aria-hidden="true">
    <div class="toast-card" data-cart-toast-text>Added to cart.</div>
</div>
@include('partials.cart-popup')
@include('partials.whatsapp-float', ['globalFooter' => $footer])
@include('partials.purchase-popups', [
    'globalPurchasePopups' => $globalPurchasePopups ?? [],
    'globalPurchasePopupProducts' => $globalPurchasePopupProducts ?? [],
])
