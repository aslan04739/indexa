<?php

namespace App\Support;

class Localized
{
    /** @return array<string, array{name: string, dir: string, hreflang: string, og: string}> */
    public static function locales(): array
    {
        return config('indexa.locales');
    }

    public static function isRtl(?string $locale = null): bool
    {
        return (self::locales()[$locale ?? app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';
    }

    /** Absolute URL of a page in a given locale. No trailing slash, matching Laravel's .htaccess redirect. */
    public static function url(string $page, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $slug = config("indexa.pages.$page.$locale");

        $path = '/'.$locale.($slug === '' ? '' : '/'.$slug);

        return rtrim(config('app.url'), '/').$path;
    }

    /**
     * hreflang alternates for a page, x-default included.
     *
     * @return array<string, string> hreflang => absolute URL
     */
    public static function alternates(string $page): array
    {
        $out = [];
        foreach (self::locales() as $code => $meta) {
            $out[$meta['hreflang']] = self::url($page, $code);
        }
        $out['x-default'] = self::url($page, config('indexa.default_locale'));

        return $out;
    }
}
