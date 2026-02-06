<?php

namespace JanDev\SeoTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoPage extends Model
{
    protected $fillable = [
        'route_name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'schema_type',
        'schema_data',
        'is_indexable',
    ];

    protected $casts = [
        'schema_data' => 'array',
        'is_indexable' => 'boolean',
    ];

    public static function findByRoute(string $routeName): ?self
    {
        if (!config('seo-tools.cache.enabled')) {
            return static::where('route_name', $routeName)->first();
        }

        return Cache::remember(
            "seo_page:{$routeName}",
            config('seo-tools.cache.ttl', 3600),
            fn () => static::where('route_name', $routeName)->first()
        );
    }

    public function toSeoArray(): array
    {
        $siteName = config('seo-tools.site_name', config('app.name'));

        return [
            'title' => $this->meta_title ?? $siteName,
            'description' => $this->meta_description ?? config('seo-tools.defaults.description'),
            'keywords' => $this->meta_keywords,
            'og_title' => $this->og_title ?? $this->meta_title ?? $siteName,
            'og_description' => $this->og_description ?? $this->meta_description ?? config('seo-tools.defaults.description'),
            'og_image' => $this->og_image ?? config('seo-tools.defaults.og_image'),
            'robots' => $this->is_indexable ? 'index, follow' : 'noindex, nofollow',
            'schema_type' => $this->schema_type,
            'schema_data' => $this->schema_data,
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $seoPage) {
            Cache::forget("seo_page:{$seoPage->route_name}");
        });

        static::deleted(function (self $seoPage) {
            Cache::forget("seo_page:{$seoPage->route_name}");
        });
    }
}
