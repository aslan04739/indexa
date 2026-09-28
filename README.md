# Indexa

Algerian marketplace for sponsored articles and backlinks on vetted Algerian websites. Prices in DZD, CIB and Edahabia payment, every link monitored for 12 months.

## Principles

- **Public pages ship no JavaScript.** Everything search engines and AI crawlers need (text, prices, metrics, JSON-LD) is in the server-rendered HTML. AI crawlers such as GPTBot, OAI-SearchBot, ClaudeBot and PerplexityBot do not execute JavaScript ([Vercel and MERJ](https://vercel.com/blog/the-rise-of-the-ai-crawler)). A test enforces this.
- **Three languages, one URL each.** `/fr`, `/ar`, `/en` with translated slugs, `hreflang` alternates and `x-default` pointing to French. Arabic renders right to left.
- **Structured data on every page.** Organization, WebSite, WebPage, BreadcrumbList; Service and FAQPage on the home page.
- JavaScript (Livewire, Alpine) is allowed only inside the logged-in app, which is not built yet.

## Stack

Laravel 13, Blade, Tailwind CSS 4 (CSS only on public pages), PHPUnit. Planned: PostgreSQL, Filament for the back office, Chargily Pay v2 for CIB and Edahabia.

## Structure

| Path | Purpose |
| --- | --- |
| `config/indexa.php` | Locales, public pages and their slug per language |
| `app/Support/Localized.php` | Localized URLs and hreflang alternates |
| `lang/{fr,ar,en}/site.php` | All public copy |
| `resources/views/layouts/public.blade.php` | Head (canonical, hreflang, OG), header, footer |
| `resources/views/partials/schema.blade.php` | JSON-LD graph |
| `routes/web.php` | One route per page and locale, `sitemap.xml`, `robots.txt` |
| `tests/Feature/PublicPagesTest.php` | SEO/GEO checks on every page and locale |

## Add a public page

1. Add the page and its three slugs to `pages` in `config/indexa.php`.
2. Add its copy under `pages.<key>` in the three `lang/*/site.php` files.
3. Create `resources/views/pages/<key>.blade.php`.

Routes, sitemap entries, hreflang and tests pick it up automatically.

## Run locally

```bash
composer install
cp .env.example .env && php artisan key:generate
npm install && npm run build
php artisan serve        # http://localhost:8000/fr
php artisan test
```

Set `APP_URL` to the production origin: canonicals, hreflang and the sitemap are built from it.
