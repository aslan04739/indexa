# Indexa

Algerian marketplace for sponsored articles and backlinks on vetted Algerian websites. Prices in DZD, CIB and Edahabia payment, every link monitored for 12 months.

## Principles

- **Public pages ship no JavaScript.** Everything search engines and AI crawlers need (text, prices, metrics, JSON-LD) is in the server-rendered HTML. AI crawlers such as GPTBot, OAI-SearchBot, ClaudeBot and PerplexityBot do not execute JavaScript ([Vercel and MERJ](https://vercel.com/blog/the-rise-of-the-ai-crawler)). A test enforces this.
- **Three languages, one URL each.** `/fr`, `/ar`, `/en` with translated slugs, `hreflang` alternates and `x-default` pointing to French. Arabic renders right to left.
- **Structured data on every page.** Organization, WebSite, WebPage, BreadcrumbList; Service and FAQPage on the home page.
- The logged-in app (`/app`) is also plain server-rendered forms, with `noindex` headers.

## What works

| Role | Flow |
| --- | --- |
| Publisher | Sign up, add a site, prove ownership with a meta tag, set price (DZD), link attribute and turnaround. Accept or refuse orders, submit the live URL, get credited on validation, request a payout (CCP, BaridiMob, bank). |
| Buyer | Sign up, browse the catalog with filters, top up the wallet by CIB or Edahabia (Chargily Pay), order with own text or a brief, validate or dispute the publication, download an invoice per top-up. |
| Admin | Approve or reject sites (with DR), resolve disputes (pay publisher or refund buyer), pay or reject payouts, see volume and commission. |
| System | Publications are accepted only if the page is live, indexable and links to the target. Orders not accepted in 3 days are cancelled and refunded; publications not answered in 5 days auto-complete. Completed links are re-checked daily for 12 months. |

Money is an append-only ledger (`wallet_transactions`, integer DZD). Buyers pay publisher price + commission (20% by default), held until validation.

## Stack

Laravel 13, Blade, Tailwind CSS 4 (CSS only), PHPUnit, Chargily Pay v2. SQLite locally, PostgreSQL recommended in production.

## Structure

| Path | Purpose |
| --- | --- |
| `config/indexa.php` | Locales, public pages and their slug per language |
| `app/Support/Localized.php` | Localized URLs and hreflang alternates |
| `lang/{fr,ar,en}/site.php` | All public copy |
| `resources/views/layouts/public.blade.php` | Head (canonical, hreflang, OG), header, footer |
| `resources/views/partials/schema.blade.php` | JSON-LD graph |
| `routes/web.php` | One route per page and locale, `sitemap.xml`, `robots.txt` |
| `config/marketplace.php` | Commission, deadlines, categories, Chargily keys |
| `app/Services/OrderWorkflow.php` | Every order state change and its money movement |
| `app/Services/LinkChecker.php` | Fetches an article, finds the link, reads rel and noindex |
| `app/Services/Chargily.php` | Checkout creation and webhook signature |
| `routes/app.php` | Logged-in marketplace |
| `routes/console.php` | `orders:deadlines` (hourly), `links:check` (daily) |
| `tests/Feature/PublicPagesTest.php` | SEO/GEO checks on every page and locale |
| `tests/Feature/MarketplaceFlowTest.php` | End-to-end marketplace flows |

## Add a public page

1. Add the page and its three slugs to `pages` in `config/indexa.php`.
2. Add its copy under `pages.<key>` in the three `lang/*/site.php` files.
3. Create `resources/views/pages/<key>.blade.php`.

Routes, sitemap entries, hreflang and tests pick it up automatically.

## Run locally

```bash
composer install
cp .env.example .env && php artisan key:generate
echo "MARKETPLACE_FAKE_PAYMENTS=true" >> .env   # top-ups credit instantly without Chargily
php artisan migrate --seed
npm install && npm run build
php artisan serve        # http://localhost:8000/fr
php artisan test
```

Demo accounts (password `password`): `admin@indexa.test`, `publisher@indexa.test`, `buyer@indexa.test`.

## Production checklist

- `APP_URL`: canonicals, hreflang, sitemap and Chargily callback URLs are built from it.
- `CHARGILY_SECRET_KEY` and `CHARGILY_MODE=live`; set the webhook URL to `https://<domain>/webhooks/chargily` in the Chargily dashboard.
- `INDEXA_LEGAL_*`: company name, address, RC, NIF, NIS printed on invoices.
- Run the scheduler: `* * * * * php artisan schedule:run`.
- Keep `MARKETPLACE_FAKE_PAYMENTS=false`.
