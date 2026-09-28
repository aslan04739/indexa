@extends('layouts.public')

@section('content')
    @php($t = "site.pages.$page")

    <article class="flex max-w-3xl flex-col gap-6">
        <h1 class="text-3xl font-bold leading-tight text-balance sm:text-4xl">{{ __("$t.h1") }}</h1>
        <p class="text-lg text-muted">{{ __("$t.intro") }}</p>
        <ul class="flex list-disc flex-col gap-2 ps-5">
            @foreach (__("$t.points") as $point)
                <li>{{ $point }}</li>
            @endforeach
        </ul>
        @if ($page !== 'about')
            <p><a href="{{ App\Support\Localized::url($page === 'advertisers' ? 'publishers' : 'advertisers') }}" class="text-brand underline">{{ __($page === 'advertisers' ? 'site.cta_publisher' : 'site.cta_advertiser') }}</a></p>
        @endif
    </article>
@endsection
