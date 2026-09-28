{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($pages as $page)
@foreach ($locales as $code => $meta)
    <url>
        <loc>{{ App\Support\Localized::url($page, $code) }}</loc>
@foreach (App\Support\Localized::alternates($page) as $hreflang => $href)
        <xhtml:link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}"/>
@endforeach
    </url>
@endforeach
@endforeach
</urlset>
