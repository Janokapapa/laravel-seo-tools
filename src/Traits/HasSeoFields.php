<?php

namespace JanDev\SeoTools\Traits;

trait HasSeoFields
{
    public function getSeoTitle(): string
    {
        return $this->meta_title ?: ($this->title ?? config('seo-tools.defaults.title'));
    }

    public function getSeoDescription(): string
    {
        return $this->meta_description ?: ($this->description ?? config('seo-tools.defaults.description'));
    }

    public function getOgImage(): string
    {
        return $this->og_image ?: config('seo-tools.defaults.og_image', '');
    }

    public function toSeoArray(): array
    {
        $siteName = config('seo-tools.site_name', config('app.name'));

        return [
            'title' => $this->getSeoTitle() . ' | ' . $siteName,
            'description' => $this->getSeoDescription(),
            'keywords' => $this->meta_keywords ?? '',
            'og_title' => $this->meta_title ?: ($this->title ?? $siteName),
            'og_description' => $this->getSeoDescription(),
            'og_image' => $this->getOgImage(),
            'robots' => 'index, follow',
            'schema_type' => null,
            'schema_data' => null,
        ];
    }
}
