<?php

namespace JanDev\SeoTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    protected $fillable = [
        'source_path',
        'destination_path',
        'status_code',
        'hits',
        'last_hit_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_hit_at' => 'datetime',
    ];

    public static function findByPath(string $path): ?self
    {
        $path = '/' . ltrim($path, '/');

        if (!config('seo-tools.cache.enabled')) {
            return static::where('source_path', $path)->where('is_active', true)->first();
        }

        return Cache::remember(
            "redirect:" . md5($path),
            config('seo-tools.cache.ttl', 3600),
            fn () => static::where('source_path', $path)->where('is_active', true)->first()
        );
    }

    public function incrementHits(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }

    protected static function booted(): void
    {
        static::saved(function (self $redirect) {
            Cache::forget("redirect:" . md5($redirect->source_path));
        });

        static::deleted(function (self $redirect) {
            Cache::forget("redirect:" . md5($redirect->source_path));
        });
    }
}
