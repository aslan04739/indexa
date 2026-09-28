<?php

namespace Tests\Feature;

use App\Support\Localized;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function pages(): array
    {
        $config = require __DIR__.'/../../config/indexa.php';
        $cases = [];
        foreach (array_keys($config['pages']) as $page) {
            foreach (array_keys($config['locales']) as $locale) {
                $cases["$locale.$page"] = [$locale, $page];
            }
        }

        return $cases;
    }

    #[DataProvider('pages')]
    public function test_page_renders_with_seo_essentials(string $locale, string $page): void
    {
        app()->setLocale($locale);
        $url = Localized::url($page, $locale);

        $html = $this->get(parse_url($url, PHP_URL_PATH))->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.$url.'">', $html);
        $this->assertSame(1, substr_count($html, '<h1'), 'exactly one H1');
        foreach (Localized::alternates($page) as $hreflang => $href) {
            $this->assertStringContainsString('hreflang="'.$hreflang.'" href="'.$href.'"', $html);
        }
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $schema = json_decode($m[1] ?? '', true);
        $this->assertSame('https://schema.org', $schema['@context'] ?? null, 'JSON-LD must be valid');
    }

    #[DataProvider('pages')]
    public function test_public_pages_ship_no_javascript(string $locale, string $page): void
    {
        $html = $this->get(parse_url(Localized::url($page, $locale), PHP_URL_PATH))->getContent();

        preg_match_all('/<script\b([^>]*)>/i', $html, $scripts);
        foreach ($scripts[1] as $attributes) {
            $this->assertStringContainsString('application/ld+json', $attributes, 'only JSON-LD scripts allowed on public pages');
        }
        $this->assertStringNotContainsString('modulepreload', $html);
    }

    public function test_arabic_is_right_to_left(): void
    {
        $this->get('/ar')->assertOk()->assertSee('dir="rtl"', false)->assertSee('lang="ar-DZ"', false);
        $this->get('/fr')->assertOk()->assertSee('dir="ltr"', false);
    }

    public function test_root_redirects_to_default_locale(): void
    {
        $this->get('/')->assertRedirect('/fr');
    }

    public function test_home_has_faq_schema(): void
    {
        $this->get('/fr')->assertSee('"@type":"FAQPage"', false);
    }

    public function test_sitemap_lists_every_page_with_alternates(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $doc = simplexml_load_string($xml);
        $this->assertNotFalse($doc);
        $this->assertCount(count(config('indexa.pages')) * count(config('indexa.locales')), $doc->url);
    }

    public function test_robots_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml');
    }
}
