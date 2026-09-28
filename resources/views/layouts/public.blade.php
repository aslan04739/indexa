@php
    use App\Support\Localized;
    $locale = app()->getLocale();
    $locales = Localized::locales();
    $t = "site.pages.$page";
    $canonical = Localized::url($page);
@endphp
<!DOCTYPE html>
<html lang="{{ $locales[$locale]['hreflang'] }}" dir="{{ $locales[$locale]['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __("$t.title") }}</title>
    <meta name="description" content="{{ __("$t.description") }}">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach (Localized::alternates($page) as $hreflang => $href)
        <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
    @endforeach

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('indexa.name') }}">
    <meta property="og:title" content="{{ __("$t.title") }}">
    <meta property="og:description" content="{{ __("$t.description") }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $locales[$locale]['og'] }}">
    <meta name="twitter:card" content="summary">

    {{-- CSS only. Public pages ship no JavaScript: everything crawlers and LLMs need is in this HTML. --}}
    @vite('resources/css/app.css')

    @include('partials.schema', ['page' => $page, 'canonical' => $canonical])
</head>
<body class="bg-paper text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:bg-paper focus:p-2">{{ $locale === 'ar' ? 'انتقل إلى المحتوى' : ($locale === 'fr' ? 'Aller au contenu' : 'Skip to content') }}</a>

    <header class="border-b border-line">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ Localized::url('home') }}" class="text-xl font-bold tracking-tight">{{ config('indexa.name') }}</a>
            <nav aria-label="Main">
                <ul class="flex flex-wrap gap-5 text-sm">
                    @foreach (['advertisers', 'publishers', 'about'] as $item)
                        <li><a href="{{ Localized::url($item) }}" @if ($item === $page) aria-current="page" class="font-semibold text-brand" @else class="hover:text-brand" @endif>{{ __("site.nav.$item") }}</a></li>
                    @endforeach
                </ul>
            </nav>
            <nav aria-label="{{ __('site.language') }}">
                <ul class="flex gap-3 text-sm">
                    @foreach ($locales as $code => $meta)
                        <li><a href="{{ Localized::url($page, $code) }}" hreflang="{{ $meta['hreflang'] }}" lang="{{ $meta['hreflang'] }}" @if ($code === $locale) aria-current="true" class="font-semibold" @else class="text-muted hover:text-brand" @endif>{{ $meta['name'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </header>

    @if ($page !== 'home')
        <nav aria-label="Breadcrumb" class="mx-auto max-w-5xl px-4 pt-6 text-sm text-muted">
            <ol class="flex gap-2">
                <li><a href="{{ Localized::url('home') }}" class="hover:text-brand">{{ __('site.breadcrumb_home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page">{{ __("site.nav.$page") }}</li>
            </ol>
        </nav>
    @endif

    <main id="main" class="mx-auto max-w-5xl px-4 py-10">
        @yield('content')
    </main>

    <footer class="border-t border-line">
        <div class="mx-auto flex max-w-5xl flex-col gap-2 px-4 py-8 text-sm text-muted">
            <p class="max-w-prose">{{ __('site.footer') }}</p>
            <p>{{ __('site.contact') }}: {{ config('indexa.contact_email') }}</p>
        </div>
    </footer>
</body>
</html>
