@php
    $popupConfig = $globalPurchasePopups ?? [];
    $popupEnabled = $popupConfig['enabled'] ?? true;
    $popupInterval = (int) ($popupConfig['interval'] ?? 12);
    $popupInterval = max(4, min(300, $popupInterval));
    $popupItems = $popupConfig['items'] ?? [];
    $popupProductMap = $globalPurchasePopupProducts ?? [];
    $popupItems = collect($popupItems)
        ->filter(fn ($item) => is_array($item))
        ->map(function ($item) {
            return [
                'name' => trim((string) ($item['name'] ?? '')),
                'product' => trim((string) ($item['product'] ?? '')),
                'time' => trim((string) ($item['time'] ?? '')),
                'image' => trim((string) ($item['image'] ?? '')),
                'url' => trim((string) ($item['url'] ?? '')),
                'product_id' => $item['product_id'] ?? null,
            ];
        })
        ->filter(fn ($item) => $item['name'] !== '' || $item['product'] !== '' || !empty($item['product_id']))
        ->map(function ($item) use ($popupProductMap) {
            $productId = $item['product_id'];
            $productKey = $productId;
            if (!$productKey) {
                $productName = strtolower(trim((string) ($item['product'] ?? '')));
                $productKey = $productName !== '' ? "name:{$productName}" : null;
            }
            $productData = $productKey && isset($popupProductMap[$productKey])
                ? $popupProductMap[$productKey]
                : null;
            $resolvedProduct = $item['product'] ?: ($productData['name'] ?? '');
            $resolvedImage = ($productData['image'] ?? '') ?: $item['image'];
            $resolvedUrl = $item['url'] ?: ($productData['url'] ?? '');

            return [
                'name' => $item['name'] ?: 'Someone',
                'product' => $resolvedProduct,
                'time' => $item['time'],
                'image' => $resolvedImage,
                'url' => $resolvedUrl,
            ];
        })
        ->values()
        ->all();
    $firstPopup = $popupItems[0] ?? null;
@endphp

@if ($popupEnabled && !empty($popupItems))
    <div class="purchase-popup"
         data-purchase-popup
         data-purchase-items='@json($popupItems)'
         data-purchase-interval="{{ $popupInterval }}">
        <button class="purchase-popup-close" type="button" aria-label="Close" data-purchase-close>x</button>
        <div class="purchase-popup-content">
            <a class="purchase-popup-media" href="{{ $firstPopup['url'] ?? '#' }}" aria-label="View {{ $firstPopup['product'] ?? 'product' }}" data-purchase-link>
                <img src="{{ $firstPopup['image'] ?? '' }}"
                     alt=""
                     data-purchase-image
                     @if (empty($firstPopup['image'] ?? '')) hidden @endif>
                <span class="purchase-popup-fallback"
                      data-purchase-fallback
                      @if (!empty($firstPopup['image'] ?? '')) hidden @endif>
                    <svg class="purchase-popup-icon" viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path class="purchase-popup-tee" d="M120 140l70-50h220l70 50 70 40-40 70-60-30v380a40 40 0 0 1-40 40H160a40 40 0 0 1-40-40V220l-60 30-40-70 70-40z"/>
                        <path class="purchase-popup-tee-shadow" d="M185 150h230l55 35-25 45-48-22v350H203V208l-48 22-25-45 55-35z"/>
                        <circle class="purchase-popup-tee-neck" cx="300" cy="135" r="35"/>
                        <rect class="purchase-popup-tee-graphic" x="215" y="260" width="170" height="160" rx="28"/>
                    </svg>
                </span>
            </a>
            <div class="purchase-popup-body">
                <p class="purchase-popup-line"><span data-purchase-name>{{ $firstPopup['name'] ?? 'Someone' }}</span> purchased</p>
                <p class="purchase-popup-product" data-purchase-product>{{ $firstPopup['product'] ?? '' }}</p>
                <p class="purchase-popup-time" data-purchase-time>{{ $firstPopup['time'] ?? '' }}</p>
            </div>
        </div>
    </div>
@endif
