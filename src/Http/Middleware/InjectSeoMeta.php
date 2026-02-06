<?php

namespace JanDev\SeoTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JanDev\SeoTools\Models\SeoPage;
use JanDev\SeoTools\Traits\HasSeoFields;
use Symfony\Component\HttpFoundation\Response;

class InjectSeoMeta
{
    public function handle(Request $request, Closure $next): Response
    {
        $seo = $this->resolveSeo($request);

        view()->share('seo', $seo);

        return $next($request);
    }

    protected function resolveSeo(Request $request): array
    {
        // Priority 1: Model with HasSeoFields trait (bound to route)
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (is_object($parameter) && in_array(HasSeoFields::class, class_uses_recursive($parameter))) {
                return $parameter->toSeoArray();
            }
        }

        // Priority 2: SeoPage record for the current route name
        $routeName = $request->route()?->getName();
        if ($routeName) {
            $seoPage = SeoPage::findByRoute($routeName);
            if ($seoPage) {
                return $seoPage->toSeoArray();
            }
        }

        // Priority 3: Config defaults
        $siteName = config('seo-tools.site_name', config('app.name'));

        return [
            'title' => config('seo-tools.defaults.title', $siteName),
            'description' => config('seo-tools.defaults.description', ''),
            'keywords' => '',
            'og_title' => config('seo-tools.defaults.title', $siteName),
            'og_description' => config('seo-tools.defaults.description', ''),
            'og_image' => config('seo-tools.defaults.og_image', ''),
            'robots' => config('seo-tools.defaults.robots', 'index, follow'),
            'schema_type' => null,
            'schema_data' => null,
        ];
    }
}
