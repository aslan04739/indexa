<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Proves site ownership: the site's home page must carry its verification meta tag. */
class SiteVerifier
{
    public function verify(Site $site): bool
    {
        try {
            $response = Http::timeout(20)->withUserAgent('IndexaBot/1.0 (+https://indexa.dz)')->get($site->url);
        } catch (Throwable) {
            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $pattern = '/<meta[^>]+name=["\']indexa-verification["\'][^>]+content=["\']'.preg_quote($site->verification_token, '/').'["\']/i';
        if (! preg_match($pattern, $response->body())) {
            return false;
        }

        $site->forceFill(['verified_at' => now()])->save();

        return true;
    }
}
