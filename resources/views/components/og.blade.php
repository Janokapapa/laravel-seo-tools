@if(isset($seo))
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? '' }}">
<meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? '' }}">
@if(!empty($seo['og_image']))
<meta property="og:image" content="{{ str_starts_with($seo['og_image'], 'http') ? $seo['og_image'] : url($seo['og_image']) }}">
@endif
<meta property="og:site_name" content="{{ config('seo-tools.site_name', config('app.name')) }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? '' }}">
<meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? '' }}">
@if(!empty($seo['og_image']))
<meta name="twitter:image" content="{{ str_starts_with($seo['og_image'], 'http') ? $seo['og_image'] : url($seo['og_image']) }}">
@endif
@endif
