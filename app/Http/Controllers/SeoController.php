<?php

namespace App\Http\Controllers;

use App\Support\Localized;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $pages = array_keys(config('indexa.pages'));

        return response()
            ->view('seo.sitemap', ['pages' => $pages, 'locales' => Localized::locales()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /app/',
            '',
            'Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml',
            '',
        ]);

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
