# Laravel SEO Tools

SEO package for Laravel 11 + Filament 4 projects. Meta tags, Open Graph, JSON-LD schema, sitemap, redirects, and Filament admin panel.

## Installation

```bash
composer require jandev/laravel-seo-tools
```

Publish the config:

```bash
php artisan vendor:publish --tag=seo-tools-config
```

Migrations run automatically (package discovery), or manually:

```bash
php artisan migrate
```

## File Structure

```
laravel-seo-tools/
├── composer.json
├── config/seo-tools.php
├── database/migrations/
│   ├── 2024_01_01_000001_create_seo_pages_table.php
│   └── 2024_01_01_000002_create_redirects_table.php
├── resources/views/components/
│   ├── head.blade.php          # canonical, meta desc, keywords, robots
│   ├── og.blade.php            # og:* + twitter card tags
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

## Components

### 1. HasSeoFields Trait

Applied to models (e.g. Service, Project). Expects these columns on the model's table:

- `meta_title` (string, nullable)
- `meta_description` (text, nullable)
- `meta_keywords` (string, nullable)
- `og_image` (string, nullable)

Methods:

```php
$model->getSeoTitle();       // meta_title ?? title ?? config default
$model->getSeoDescription(); // meta_description ?? description ?? config default
$model->getOgImage();        // og_image ?? config default
$model->toSeoArray();        // full SEO array for middleware/views
```

Fallback logic: if `meta_title` is empty, uses the model's `title` field. If that's also empty, falls back to config default.

### 2. SeoPage Model

For static pages (home, contact, projects) that don't have model binding.

Table: `seo_pages`

| Column | Type | Description |
|--------|------|-------------|
| route_name | string, unique | Laravel route name (e.g. "home", "contact") |
| meta_title | string, nullable | |
| meta_description | text, nullable | |
| meta_keywords | string, nullable | |
| og_title | string, nullable | |
| og_description | text, nullable | |
| og_image | string, nullable | |
| schema_type | string, nullable | WebPage, FAQPage, ContactPage, etc. |
| schema_data | JSON, nullable | Custom schema.org data |
| is_indexable | boolean, default true | false = noindex, nofollow |

Cache: 1 hour, by route_name. Automatically cleared on save/delete.

```php
$seoPage = SeoPage::findByRoute('home'); // cached
$seoPage->toSeoArray(); // same format as HasSeoFields
```

### 3. Redirect Model

301/302 redirects with hit counter.

Table: `redirects`

| Column | Type | Description |
|--------|------|-------------|
| source_path | string, unique | From path (e.g. "/old-page") |
| destination_path | string | To path (e.g. "/new-page" or full URL) |
| status_code | int, default 301 | 301 or 302 |
| hits | unsigned int, default 0 | Times used |
| last_hit_at | timestamp, nullable | Last used at |
| is_active | boolean, default true | Can be toggled on/off |

Cache: 1 hour, by path.

### 4. InjectSeoMeta Middleware

Automatically shares the `$seo` variable with all views. Priority:

1. **Model with HasSeoFields trait** - if the route has model binding and the model uses the trait
2. **SeoPage record** - if there's a `seo_pages` entry for the current route_name
3. **Config defaults** - `seo-tools.defaults.*`

The `$seo` array fields:

```php
[
    'title' => '...',
    'description' => '...',
    'keywords' => '...',
    'og_title' => '...',
    'og_description' => '...',
    'og_image' => '...',
    'robots' => 'index, follow',
    'schema_type' => null,  // from SeoPage
    'schema_data' => null,  // from SeoPage
]
```

### 5. HandleRedirects Middleware

Checks the `redirects` table on every request. If a match is found and active, redirects and increments the hit counter.

### 6. Blade Components

Use in your layout:

```blade
<head>
    <title>{{ $seo['title'] ?? 'Default Title' }}</title>
    <x-seo::head />    {{-- canonical, meta description, keywords, robots --}}
    <x-seo::og />      {{-- og:*, twitter card --}}
    <x-seo::schema />  {{-- JSON-LD: custom schema + Organization --}}
</head>
```

Breadcrumbs (within a page):

```blade
<x-seo::breadcrumbs :items="[
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Services', 'url' => '/#services'],
    ['name' => $service->title],
]" />
```

### 7. SeoFieldsSection (Filament)

Reusable form section for any Filament resource:

```php
use JanDev\SeoTools\Filament\Components\SeoFieldsSection;

// In a resource form:
SeoFieldsSection::make()
```

Includes:
- meta_title (max 60 char, with helper text)
- meta_description (max 160 char, textarea, with helper text)
- meta_keywords (comma-separated)
- og_image (URL field)

Collapsible section, titled "SEO", with magnifying glass icon.

### 8. Filament Resources

- **SeoPageResource** (`/admin/seo-pages`) - CRUD for static page SEO data
- **RedirectResource** (`/admin/redirects`) - CRUD for redirects with hit counter display

Both appear under the "SEO" navigation group.

### 9. Artisan Commands

```bash
# Generate sitemap from config
php artisan seo:sitemap

# Add SEO fields to an existing table (generates migration stub)
php artisan seo:add-fields services
# -> Creates: database/migrations/xxxx_add_seo_fields_to_services_table.php
```

## Configuration

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
            'same_as' => [], // social profile URLs
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
        'ttl' => 3600, // 1 hour
    ],
];
```

## Integration into a New Project

### 1. Composer + Config

```bash
composer require jandev/laravel-seo-tools
php artisan vendor:publish --tag=seo-tools-config
php artisan migrate
```

### 2. Register Middleware (`bootstrap/app.php`)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \JanDev\SeoTools\Http\Middleware\HandleRedirects::class,
        \JanDev\SeoTools\Http\Middleware\InjectSeoMeta::class,
    ]);
})
```

### 3. Register Filament Plugin (`AdminPanelProvider.php`)

```php
use JanDev\SeoTools\Filament\SeoToolsPlugin;

->plugins([
    SeoToolsPlugin::make(),
])
```

### 4. Update Layout

```blade
<title>{{ $seo['title'] ?? 'Default' }}</title>
<x-seo::head />
<x-seo::og />
<x-seo::schema />
```

### 5. Add SEO Fields to Models

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
        // ... existing fields ...
        'meta_title', 'meta_description', 'meta_keywords', 'og_image',
    ];
}
```

### 6. Add SEO Section to Filament Forms

```php
use JanDev\SeoTools\Filament\Components\SeoFieldsSection;

// In form schema:
SeoFieldsSection::make()
```

### 7. Schedule Sitemap Generation (`routes/console.php`)

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('seo:sitemap')->daily();
```

### 8. Update robots.txt

```
User-agent: *
Disallow: /admin/

Sitemap: https://yourdomain.com/sitemap.xml
```

## jandev-laravel Specific Details

### Service Table Extension

Service page content (previously hardcoded in blade templates) was moved to the database:

| Column | Type | Description |
|--------|------|-------------|
| faqs | JSON | `[{question, answer}, ...]` |
| benefits | JSON | `[{icon, title, description}, ...]` |
| process_steps | JSON | `[{title, description}, ...]` |
| primary_color | string | Hex color (e.g. `#6366f1`) |
| secondary_color | string | Hex color (e.g. `#ec4899`) |
| gradient_color | string | CSS gradient (e.g. `linear-gradient(135deg, #6366f1, #ec4899)`) |

Migrations:
- `2026_02_06_104920_add_content_and_seo_fields_to_services_table`
- `2026_02_06_104934_add_seo_fields_to_projects_table`
- `2026_02_06_105057_seed_service_content_data` (hardcoded content -> DB)

### ServiceForm (Filament)

Tabs layout:
- **General**: title, slug, icon, description, content, technologies, features, is_active, sort_order
- **Content**: Repeater FAQs, Repeater Benefits, Repeater Process Steps
- **Appearance**: ColorPicker (primary, secondary, gradient)
- **SEO**: SeoFieldsSection

### service.blade.php Refactoring

- Hardcoded `$colors`, `$faqs`, `$benefits` PHP arrays removed
- Replaced with: `$service->faqs`, `$service->benefits`, `$service->process_steps`, `$service->primary_color`
- Hex-to-RGB conversion for CSS rgba() functions done in PHP
- JSON-LD schema added: Service + FAQPage + BreadcrumbList
- FAQ keys changed: `question`/`answer` (previously `q`/`a`)
- Benefit keys changed: `description` (previously `desc`)

### Colors per Service

| Service | Primary | Secondary | Gradient |
|---------|---------|-----------|----------|
| web-development | #6366f1 | #ec4899 | 135deg indigo -> pink |
| ubuntu-admin | #E95420 | #ff7043 | 135deg ubuntu orange -> light orange |
| devops | #06b6d4 | #10b981 | 135deg cyan -> emerald |

## Technical Notes

- Filament 4 property types: `string|BackedEnum|null` (navigationIcon), `string|UnitEnum|null` (navigationGroup)
- The package uses `Heroicon::OutlinedDocumentText` and `Heroicon::OutlinedArrowUturnRight` enums for icons
- Cache is automatically cleared on model save/delete (via booted() events)
- Sitemap is a `spatie/laravel-sitemap` wrapper, automatically uses model scope (`scopeActive`) if it exists
- For local development use path repo (symlink), for production use VCS repo in composer.json
