<?php

return [

    'name' => 'Indexa',

    // Supported locales. The first one is the default and the x-default target.
    'locales' => [
        'fr' => ['name' => 'Français', 'dir' => 'ltr', 'hreflang' => 'fr-DZ', 'og' => 'fr_DZ'],
        'ar' => ['name' => 'العربية', 'dir' => 'rtl', 'hreflang' => 'ar-DZ', 'og' => 'ar_DZ'],
        'en' => ['name' => 'English', 'dir' => 'ltr', 'hreflang' => 'en', 'og' => 'en_US'],
    ],

    'default_locale' => 'fr',

    // Public pages and their slug per locale. An empty slug is the locale home.
    // Arabic keeps Latin slugs: readable when shared, no percent-encoding in logs or reports.
    'pages' => [
        'home' => ['fr' => '', 'ar' => '', 'en' => ''],
        'catalog' => ['fr' => 'catalogue', 'ar' => 'dalil', 'en' => 'catalog'],
        'advertisers' => ['fr' => 'annonceurs', 'ar' => 'moualinin', 'en' => 'advertisers'],
        'publishers' => ['fr' => 'editeurs', 'ar' => 'nashirin', 'en' => 'publishers'],
        'about' => ['fr' => 'a-propos', 'ar' => 'man-nahnu', 'en' => 'about'],
    ],

    // Seller identity printed on invoices (law 18-05 requires it on the site and on invoices).
    'legal' => [
        'name' => env('INDEXA_LEGAL_NAME', 'Indexa'),
        'address' => env('INDEXA_LEGAL_ADDRESS', ''),
        'rc' => env('INDEXA_RC', ''),
        'nif' => env('INDEXA_NIF', ''),
        'nis' => env('INDEXA_NIS', ''),
    ],

    'contact_email' => env('INDEXA_CONTACT_EMAIL', 'contact@indexa.dz'),

    // Profiles used in Organization.sameAs (entity consolidation for search engines and LLMs).
    'same_as' => array_values(array_filter([
        env('INDEXA_LINKEDIN_URL'),
    ])),

];
