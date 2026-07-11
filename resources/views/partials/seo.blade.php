@php
    $seoTitle = trim((string) ($title ?? $metaTitle ?? config('app.name', 'Nai Prints')));
    $seoDescription = trim(strip_tags((string) ($description ?? $metaDescription ?? 'Nai Prints creates custom t-shirts, hoodies, uniforms, embroidery, jerseys, and branded merchandise in Kenya.')));
    $seoDescription = \Illuminate\Support\Str::limit($seoDescription, 155, '');
    $seoCanonical = (string) ($canonical ?? url()->current());
    $seoRobots = (string) ($robots ?? 'index, follow, max-image-preview:large');
    $seoType = (string) ($type ?? 'website');
    $seoImage = $image ?? url('/images/logo.svg');
    if (is_string($seoImage) && $seoImage !== '' && !str_starts_with($seoImage, 'http')) {
        $seoImage = url($seoImage);
    }
    $seoFavicon = $favicon ?? ($globalHeader['favicon_url'] ?? '/images/logo.svg');
    if (is_string($seoFavicon) && $seoFavicon !== '' && !str_starts_with($seoFavicon, 'http')) {
        $seoFavicon = url($seoFavicon);
    }
    $schemaData = $schema ?? null;
@endphp

<link rel="icon" href="{{ $seoFavicon }}">
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">
<meta property="og:site_name" content="Nai Prints">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
@if (!empty($seoImage))
<meta property="og:image" content="{{ $seoImage }}">
@endif
<meta name="twitter:card" content="{{ !empty($seoImage) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if (!empty($seoImage))
<meta name="twitter:image" content="{{ $seoImage }}">
@endif
@if (!empty($schemaData))
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
