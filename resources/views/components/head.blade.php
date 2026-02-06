@if(isset($seo))
<link rel="canonical" href="{{ url()->current() }}">
<meta name="description" content="{{ $seo['description'] ?? '' }}">
@if(!empty($seo['keywords']))
<meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
@endif
