<?php

use App\Http\Controllers\App\WalletController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Root: send people and crawlers to the default language. Declared as x-default in hreflang.
Route::get('/', fn () => redirect('/'.config('indexa.default_locale'), 302));

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// One route per page and locale, each with its own translated slug.
foreach (config('indexa.locales') as $locale => $meta) {
    Route::prefix($locale)
        ->middleware(SetLocale::class.':'.$locale)
        ->group(function () use ($locale) {
            foreach (config('indexa.pages') as $page => $slugs) {
                Route::get($slugs[$locale] === '' ? '/' : $slugs[$locale], [PageController::class, 'show'])
                    ->defaults('page', $page)
                    ->name("$locale.$page");
            }
        });
}

Route::post('/webhooks/chargily', [WalletController::class, 'webhook'])->name('webhooks.chargily');
