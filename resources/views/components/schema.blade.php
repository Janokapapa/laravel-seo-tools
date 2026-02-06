@if(isset($seo) && !empty($seo['schema_type']))
@php
    $schema = $seo['schema_data'] ?? [];
    $schema['@context'] = 'https://schema.org';
    $schema['@type'] = $seo['schema_type'];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif

@php
    $org = config('seo-tools.schema.organization');
@endphp
@if(!empty($org['name']))
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $org['name'],
    'url' => $org['url'] ?? url('/'),
    'logo' => !empty($org['logo']) ? (str_starts_with($org['logo'], 'http') ? $org['logo'] : url($org['logo'])) : null,
    'email' => $org['email'] ?? null,
    'sameAs' => !empty($org['same_as']) ? $org['same_as'] : null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif
