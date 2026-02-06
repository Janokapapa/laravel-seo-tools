<?php

namespace JanDev\SeoTools;

use Illuminate\Support\ServiceProvider;

class SeoToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/seo-tools.php',
            'seo-tools'
        );
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/seo-tools.php' => config_path('seo-tools.php'),
        ], 'seo-tools-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'seo-tools-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/seo-tools'),
        ], 'seo-tools-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'seo');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GenerateSitemapCommand::class,
                Console\AddSeoFieldsCommand::class,
            ]);
        }
    }
}
