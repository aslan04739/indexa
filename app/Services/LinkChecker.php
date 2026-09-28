<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Fetches a published article and checks that it links to the buyer's target URL and is indexable. */
class LinkChecker
{
    public function check(string $pageUrl, string $targetUrl): LinkCheckResult
    {
        try {
            $response = Http::timeout(20)
                ->withUserAgent('IndexaBot/1.0 (+https://indexa.dz)')
                ->get($pageUrl);
        } catch (Throwable $e) {
            return new LinkCheckResult(LinkCheckResult::UNREACHABLE, detail: 'network error');
        }

        if (! $response->successful()) {
            return new LinkCheckResult(LinkCheckResult::UNREACHABLE, detail: 'HTTP '.$response->status());
        }

        $xpath = $this->parse($response->body());
        $rel = $this->findLinkRel($xpath, $targetUrl);

        if ($rel === null) {
            return new LinkCheckResult(LinkCheckResult::MISSING, detail: 'link to target not found');
        }

        $robotsHeader = strtolower($response->header('X-Robots-Tag'));
        if (str_contains($robotsHeader, 'noindex') || $this->hasNoindexMeta($xpath)) {
            return new LinkCheckResult(LinkCheckResult::NOINDEX, $rel, 'page is noindex');
        }

        return new LinkCheckResult(LinkCheckResult::OK, $rel);
    }

    /** Returns the link's rel ('' when none) if the page links to the target, null otherwise. */
    private function findLinkRel(DOMXPath $xpath, string $targetUrl): ?string
    {
        $target = self::normalize($targetUrl);

        foreach ($xpath->query('//a[@href]') as $a) {
            if (self::normalize($a->getAttribute('href')) === $target) {
                return strtolower(trim($a->getAttribute('rel')));
            }
        }

        return null;
    }

    private function hasNoindexMeta(DOMXPath $xpath): bool
    {
        foreach ($xpath->query('//meta[@name]') as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            if (in_array($name, ['robots', 'googlebot'], true) && str_contains(strtolower($meta->getAttribute('content')), 'noindex')) {
                return true;
            }
        }

        return false;
    }

    private function parse(string $html): DOMXPath
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    /** Compare URLs ignoring scheme, www., trailing slash and fragment. */
    public static function normalize(string $url): string
    {
        $url = strtolower(trim($url));
        $url = preg_replace('/#.*$/', '', $url);
        $url = preg_replace('#^https?://(www\.)?#', '', $url);

        return rtrim($url, '/');
    }
}
