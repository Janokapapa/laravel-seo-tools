<?php

namespace JanDev\SeoTools\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JanDev\SeoTools\Filament\Resources\SeoPageResource;
use JanDev\SeoTools\Filament\Resources\RedirectResource;

class SeoToolsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'seo-tools';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            SeoPageResource::class,
            RedirectResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}
