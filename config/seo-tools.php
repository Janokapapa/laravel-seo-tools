<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    */
    'site_name' => env('SEO_SITE_NAME', config('app.name')),

    /*
    |--------------------------------------------------------------------------
    | Default SEO Values
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'title' => env('SEO_DEFAULT_TITLE', 'Welcome'),
        'description' => env('SEO_DEFAULT_DESCRIPTION', ''),
        'og_image' => env('SEO_DEFAULT_OG_IMAGE', ''),
        'robots' => env('SEO_DEFAULT_ROBOTS', 'index, follow'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema.org Organization Data
    |--------------------------------------------------------------------------
    */
    'schema' => [
        'organization' => [
            'name' => env('SEO_ORG_NAME', ''),
            'url' => env('SEO_ORG_URL', ''),
            'logo' => env('SEO_ORG_LOGO', ''),
            'email' => env('SEO_ORG_EMAIL', ''),
            'same_as' => [], // Social profile URLs
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'path' => public_path('sitemap.xml'),

        // Static URLs to include
        'static_urls' => [
            // ['url' => '/', 'priority' => 1.0, 'changefreq' => 'weekly'],
        ],

        // Models to include (must have getRouteKeyName() and a route)
        'models' => [
            // ['model' => \App\Models\Service::class, 'route' => 'service', 'priority' => 0.8, 'changefreq' => 'monthly'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Integration
    |--------------------------------------------------------------------------
    */
    'filament' => [
        'navigation_group' => 'SEO',
        'navigation_icon' => 'heroicon-o-magnifying-glass',
    ],
];
