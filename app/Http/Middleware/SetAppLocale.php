<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The logged-in app has no locale in its URLs: use the user's choice, then the session, then French. */
class SetAppLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? $request->session()->get('locale', config('indexa.default_locale'));

        if (array_key_exists($locale, config('indexa.locales'))) {
            app()->setLocale($locale);
        }

        $response = $next($request);

        // App pages are private: keep them out of every index.
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
