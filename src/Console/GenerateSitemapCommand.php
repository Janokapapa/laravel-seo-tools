<?php

namespace JanDev\SeoTools\Console;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'seo:sitemap';

    protected $description = 'Generate sitemap.xml from config';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add static URLs
        $staticUrls = config('seo-tools.sitemap.static_urls', []);
        foreach ($staticUrls as $entry) {
            $sitemap->add(
                Url::create($entry['url'])
                    ->setPriority($entry['priority'] ?? 0.5)
                    ->setChangeFrequency($entry['changefreq'] ?? 'monthly')
            );
        }

        // Add model URLs
        $models = config('seo-tools.sitemap.models', []);
        foreach ($models as $entry) {
            $modelClass = $entry['model'];
            $routeName = $entry['route'];
            $priority = $entry['priority'] ?? 0.5;
            $changefreq = $entry['changefreq'] ?? 'monthly';

            $query = $modelClass::query();
            if (method_exists($modelClass, 'scopeActive')) {
                $query->active();
            }

            $query->each(function ($model) use ($sitemap, $routeName, $priority, $changefreq) {
                $url = route($routeName, $model);
                $sitemap->add(
                    Url::create($url)
                        ->setPriority($priority)
                        ->setChangeFrequency($changefreq)
                        ->setLastModificationDate($model->updated_at)
                );
            });
        }

        $path = config('seo-tools.sitemap.path', public_path('sitemap.xml'));
        $sitemap->writeToFile($path);

        $this->info("Sitemap generated at: {$path}");

        return self::SUCCESS;
    }
}
