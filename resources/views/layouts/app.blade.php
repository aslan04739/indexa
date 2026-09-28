@php
    $locale = app()->getLocale();
    $locales = config('indexa.locales');
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ $locales[$locale]['hreflang'] }}" dir="{{ $locales[$locale]['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · Indexa</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-paper text-ink antialiased">
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
            <a href="{{ $user ? route('app.dashboard') : url('/') }}" class="text-lg font-bold">Indexa</a>
            @auth
                <nav aria-label="App">
                    <ul class="flex flex-wrap gap-4 text-sm">
                        @if ($user->isBuyer())
                            <li><a class="hover:text-brand" href="{{ route('app.catalog') }}">{{ __('Catalogue') }}</a></li>
                        @endif
                        @if ($user->isPublisher())
                            <li><a class="hover:text-brand" href="{{ route('app.sites.index') }}">{{ __('Mes sites') }}</a></li>
                        @endif
                        <li><a class="hover:text-brand" href="{{ route('app.orders.index') }}">{{ __('Commandes') }}</a></li>
                        <li><a class="hover:text-brand" href="{{ route('app.wallet') }}">{{ __('Portefeuille') }}</a></li>
                        @if ($user->isPublisher())
                            <li><a class="hover:text-brand" href="{{ route('app.payouts.index') }}">{{ __('Retraits') }}</a></li>
                        @endif
                        @if ($user->isAdmin())
                            <li><a class="hover:text-brand" href="{{ route('app.admin.index') }}">{{ __('Administration') }}</a></li>
                        @endif
                    </ul>
                </nav>
            @endauth
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <form method="POST" action="{{ route('app.locale') }}" class="flex gap-1">
                    @csrf
                    @foreach ($locales as $code => $meta)
                        <button name="locale" value="{{ $code }}" lang="{{ $meta['hreflang'] }}" @class(['px-1', 'font-semibold' => $code === $locale, 'text-muted hover:text-brand' => $code !== $locale])>{{ strtoupper($code) }}</button>
                    @endforeach
                </form>
                @auth
                    <span class="text-muted">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('app.logout') }}">@csrf<button class="underline hover:text-brand">{{ __('Déconnexion') }}</button></form>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-8">
        @if (session('status'))
            <p role="status" class="rounded border border-brand bg-brand/10 px-4 py-3">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded border border-red-700 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
