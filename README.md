# Laravel SEO Tools

SEO csomag Laravel 11 + Filament 4 projektekhez. Meta tagek, Open Graph, JSON-LD schema, sitemap, redirectek, Filament admin panel.

## Telepites

```bash
composer require jandev/laravel-seo-tools
```

Config publikalas:

```bash
php artisan vendor:publish --tag=seo-tools-config
```

Migraciok automatikusan futnak (package discovery), vagy manualis:

```bash
php artisan migrate
```

## Fajlstruktura

```
laravel-seo-tools/
├── composer.json
├── config/seo-tools.php
├── database/migrations/
│   ├── 2024_01_01_000001_create_seo_pages_table.php
│   └── 2024_01_01_000002_create_redirects_table.php
├── resources/views/components/
│   ├── head.blade.php          # canonical, meta desc, keywords, robots
│   ├── og.blade.php            # og:* + twitter card tagek
│   ├── schema.blade.php        # JSON-LD output (custom + Organization)
│   └── breadcrumbs.blade.php   # BreadcrumbList JSON-LD
└── src/
    ├── SeoToolsServiceProvider.php
    ├── Traits/HasSeoFields.php
    ├── Models/
    │   ├── SeoPage.php
    │   └── Redirect.php
    ├── Http/Middleware/
    │   ├── InjectSeoMeta.php
    │   └── HandleRedirects.php
    ├── Console/
    │   ├── GenerateSitemapCommand.php
    │   └── AddSeoFieldsCommand.php
    └── Filament/
        ├── SeoToolsPlugin.php
        ├── Components/SeoFieldsSection.php
        └── Resources/
            ├── SeoPageResource.php
            └── RedirectResource.php
```

## Komponensek

### 1. HasSeoFields Trait

Modellekre rakjuk (pl. Service, Project). Feltetelezi, hogy a modell tablaján vannak ezek a mezok:

- `meta_title` (string, nullable)
- `meta_description` (text, nullable)
- `meta_keywords` (string, nullable)
- `og_image` (string, nullable)

Metodusok:

```php
$model->getSeoTitle();       // meta_title ?? title ?? config default
$model->getSeoDescription(); // meta_description ?? description ?? config default
$model->getOgImage();        // og_image ?? config default
$model->toSeoArray();        // teljes SEO tomb a middleware/view szamara
```

Fallback logika: ha `meta_title` ures, a modell `title` mezojat hasznalja. Ha az is ures, config default.

### 2. SeoPage Model

Statikus oldalakhoz (home, contact, projects) - amelyeknek nincs modell bindingje.

Tabla: `seo_pages`

| Mezo | Tipus | Leiras |
|------|-------|--------|
| route_name | string, unique | Laravel route nev (pl. "home", "contact") |
| meta_title | string, nullable | |
| meta_description | text, nullable | |
| meta_keywords | string, nullable | |
| og_title | string, nullable | |
| og_description | text, nullable | |
| og_image | string, nullable | |
| schema_type | string, nullable | WebPage, FAQPage, ContactPage, stb. |
| schema_data | JSON, nullable | Egyedi schema.org adat |
| is_indexable | boolean, default true | false = noindex, nofollow |

Cache: 1 ora, route_name alapjan. Automatikusan torlodik save/delete eseten.

```php
$seoPage = SeoPage::findByRoute('home'); // cached
$seoPage->toSeoArray(); // ugyanaz a formatum mint HasSeoFields
```

### 3. Redirect Model

301/302 atiranyitasok + hit counter.

Tabla: `redirects`

| Mezo | Tipus | Leiras |
|------|-------|--------|
| source_path | string, unique | Honnan (pl. "/regi-oldal") |
| destination_path | string | Hova (pl. "/uj-oldal" vagy full URL) |
| status_code | int, default 301 | 301 vagy 302 |
| hits | unsigned int, default 0 | Hanyszor lett hasznalva |
| last_hit_at | timestamp, nullable | Utolso hasznalat |
| is_active | boolean, default true | Ki/be kapcsolhato |

Cache: 1 ora, path alapjan.

### 4. InjectSeoMeta Middleware

Automatikusan megoszja a `$seo` valtozot minden view-val. Prioritas:

1. **Model HasSeoFields trait-tel** - ha a route-ban van model binding es a modell hasznalja a traitet
2. **SeoPage rekord** - ha van `seo_pages` bejegyzes az aktualis route_name-hez
3. **Config defaults** - `seo-tools.defaults.*`

A `$seo` tomb mezoi:

```php
[
    'title' => '...',
    'description' => '...',
    'keywords' => '...',
    'og_title' => '...',
    'og_description' => '...',
    'og_image' => '...',
    'robots' => 'index, follow',
    'schema_type' => null,  // SeoPage-bol jon
    'schema_data' => null,  // SeoPage-bol jon
]
```

### 5. HandleRedirects Middleware

Ellenorzi a `redirects` tablat minden requestnel. Ha talalat van es aktiv, atiranyit es noveli a hit countert.

### 6. Blade Komponensek

A layoutban hasznaljuk:

```blade
<head>
    <title>{{ $seo['title'] ?? 'Default Title' }}</title>
    <x-seo::head />    {{-- canonical, meta description, keywords, robots --}}
    <x-seo::og />      {{-- og:*, twitter card --}}
    <x-seo::schema />  {{-- JSON-LD: custom schema + Organization --}}
</head>
```

Breadcrumbs (oldalon belul):

```blade
<x-seo::breadcrumbs :items="[
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Services', 'url' => '/#services'],
    ['name' => $service->title],
]" />
```

### 7. SeoFieldsSection (Filament)

Ujrahasznalhato form section barmely Filament resource-hoz:

```php
use JanDev\SeoTools\Filament\Components\SeoFieldsSection;

// Resource formban:
SeoFieldsSection::make()
```

Tartalmazza:
- meta_title (max 60 char, helper text)
- meta_description (max 160 char, textarea, helper text)
- meta_keywords (comma-separated)
- og_image (URL mezo)

Collapsible section, "SEO" cimmel, magnifying glass iconnal.

### 8. Filament Resources

- **SeoPageResource** (`/admin/seo-pages`) - CRUD statikus oldalak SEO adataihoz
- **RedirectResource** (`/admin/redirects`) - CRUD redirectekhez, hit counter megjelenitese

Mindketto az "SEO" navigation group alatt jelenik meg.

### 9. Artisan Commands

```bash
# Sitemap generalas a configbol
php artisan seo:sitemap

# SEO mezok hozzaadasa egy tablához (migration stub)
php artisan seo:add-fields services
# -> Letrehozza: database/migrations/xxxx_add_seo_fields_to_services_table.php
```

## Konfiguracio

`config/seo-tools.php`:

```php
return [
    'site_name' => 'Jan Developments',

    'defaults' => [
        'title' => 'Jan Developments | Web Development',
        'description' => 'Full-stack web development...',
        'og_image' => '/logo-nav.webp',
        'robots' => 'index, follow',
    ],

    'schema' => [
        'organization' => [
            'name' => 'Jan Developments',
            'url' => 'https://jandev.eu',
            'logo' => '/logo-nav.webp',
            'email' => 'jan@jandev.eu',
            'same_as' => [], // social URLs
        ],
    ],

    'sitemap' => [
        'path' => public_path('sitemap.xml'),
        'static_urls' => [
            ['url' => '/', 'priority' => 1.0, 'changefreq' => 'weekly'],
            ['url' => '/projects', 'priority' => 0.8, 'changefreq' => 'weekly'],
            ['url' => '/contact', 'priority' => 0.6, 'changefreq' => 'monthly'],
        ],
        'models' => [
            ['model' => \App\Models\Service::class, 'route' => 'service', 'priority' => 0.9, 'changefreq' => 'monthly'],
            ['model' => \App\Models\Project::class, 'route' => 'project', 'priority' => 0.7, 'changefreq' => 'monthly'],
        ],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 ora
    ],
];
```

## Integracio uj projektbe

### 1. Composer + config

```bash
composer require jandev/laravel-seo-tools
php artisan vendor:publish --tag=seo-tools-config
php artisan migrate
```

### 2. Middleware regisztracio (`bootstrap/app.php`)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \JanDev\SeoTools\Http\Middleware\HandleRedirects::class,
        \JanDev\SeoTools\Http\Middleware\InjectSeoMeta::class,
    ]);
})
```

### 3. Filament plugin (`AdminPanelProvider.php`)

```php
use JanDev\SeoTools\Filament\SeoToolsPlugin;

->plugins([
    SeoToolsPlugin::make(),
])
```

### 4. Layout frissites

```blade
<title>{{ $seo['title'] ?? 'Default' }}</title>
<x-seo::head />
<x-seo::og />
<x-seo::schema />
```

### 5. Modellekre SEO mezok

```bash
php artisan seo:add-fields services
php artisan migrate
```

```php
// app/Models/Service.php
use JanDev\SeoTools\Traits\HasSeoFields;

class Service extends Model
{
    use HasSeoFields;

    protected $fillable = [
        // ... meglevo mezok ...
        'meta_title', 'meta_description', 'meta_keywords', 'og_image',
    ];
}
```

### 6. Filament formba SEO section

```php
use JanDev\SeoTools\Filament\Components\SeoFieldsSection;

// Form schema-ban:
SeoFieldsSection::make()
```

### 7. Sitemap scheduling (`routes/console.php`)

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('seo:sitemap')->daily();
```

### 8. robots.txt

```
User-agent: *
Disallow: /admin/

Sitemap: https://yourdomain.com/sitemap.xml
```

## jandev-laravel specifikus dolgok

### Service tabla bovites

A service oldalak tartalma (ami korabban hardcoded volt a blade-ben) DB-be kerult:

| Mezo | Tipus | Leiras |
|------|-------|--------|
| faqs | JSON | `[{question, answer}, ...]` |
| benefits | JSON | `[{icon, title, description}, ...]` |
| process_steps | JSON | `[{title, description}, ...]` |
| primary_color | string | Hex szin (pl. `#6366f1`) |
| secondary_color | string | Hex szin (pl. `#ec4899`) |
| gradient_color | string | CSS gradient (pl. `linear-gradient(135deg, #6366f1, #ec4899)`) |

Migraciok:
- `2026_02_06_104920_add_content_and_seo_fields_to_services_table`
- `2026_02_06_104934_add_seo_fields_to_projects_table`
- `2026_02_06_105057_seed_service_content_data` (hardcoded content -> DB)

### ServiceForm (Filament)

Tabs layout:
- **General**: title, slug, icon, description, content, technologies, features, is_active, sort_order
- **Content**: Repeater FAQs, Repeater Benefits, Repeater Process Steps
- **Appearance**: ColorPicker (primary, secondary, gradient)
- **SEO**: SeoFieldsSection

### service.blade.php refaktor

- Hardcoded `$colors`, `$faqs`, `$benefits` PHP tombok torolve
- Helyuk: `$service->faqs`, `$service->benefits`, `$service->process_steps`, `$service->primary_color`
- Hex-to-RGB konverzio a CSS rgba() fuggvenyekhez PHP-ban
- JSON-LD schema hozzaadva: Service + FAQPage + BreadcrumbList
- FAQ kulcsok: `question`/`answer` (korabban `q`/`a`)
- Benefit kulcsok: `description` (korabban `desc`)

### Szinek per service

| Service | Primary | Secondary | Gradient |
|---------|---------|-----------|----------|
| web-development | #6366f1 | #ec4899 | 135deg indigo->pink |
| ubuntu-admin | #E95420 | #ff7043 | 135deg ubuntu orange->light orange |
| devops | #06b6d4 | #10b981 | 135deg cyan->emerald |

## Technikai megjegyzesek

- Filament 4 property tipusok: `string|BackedEnum|null` (navigationIcon), `string|UnitEnum|null` (navigationGroup)
- A csomag `Heroicon::OutlinedDocumentText` es `Heroicon::OutlinedArrowUturnRight` enumokat hasznal ikonokhoz
- Cache automatikusan torlodik model save/delete eseten (booted() events)
- A sitemap `spatie/laravel-sitemap` wrappere, model scope-ot (`scopeActive`) automatikusan hasznalja ha letezik
- Lokalis fejleszteshez path repo (symlink), production-hoz VCS repo a composer.json-ban
