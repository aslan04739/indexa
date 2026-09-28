@php
    use App\Support\Localized;
    $locale = app()->getLocale();
    $base = rtrim(config('app.url'), '/');
    $orgId = $base.'/#organization';

    $graph = [
        array_filter([
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => config('indexa.name'),
            'url' => $base,
            'description' => __('site.tagline'),
            'areaServed' => ['@type' => 'Country', 'name' => 'Algeria'],
            'email' => config('indexa.contact_email'),
            'sameAs' => config('indexa.same_as') ?: null,
        ]),
        [
            '@type' => 'WebSite',
            '@id' => $base.'/#website',
            'url' => $base,
            'name' => config('indexa.name'),
            'publisher' => ['@id' => $orgId],
            'inLanguage' => array_column(Localized::locales(), 'hreflang'),
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => __("site.pages.$page.title"),
            'description' => __("site.pages.$page.description"),
            'inLanguage' => Localized::locales()[$locale]['hreflang'],
            'isPartOf' => ['@id' => $base.'/#website'],
            'about' => ['@id' => $orgId],
        ],
    ];

    if ($page !== 'home') {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('site.breadcrumb_home'), 'item' => Localized::url('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __("site.nav.$page"), 'item' => $canonical],
            ],
        ];
    }

    if ($page === 'home') {
        $graph[] = [
            '@type' => 'Service',
            'name' => __('site.pages.home.h1'),
            'description' => __('site.pages.home.intro'),
            'provider' => ['@id' => $orgId],
            'areaServed' => ['@type' => 'Country', 'name' => 'Algeria'],
            'serviceType' => 'Sponsored content and link building',
        ];
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => [
                '@type' => 'Question',
                'name' => $qa[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
            ], __('site.pages.home.faq')),
        ];
    }

    $jsonLd = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
@endphp
<script type="application/ld+json">{!! $jsonLd !!}</script>
