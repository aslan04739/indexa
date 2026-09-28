@extends('layouts.public')

@section('content')
    @php($t = 'site.pages.catalog')

    <div class="flex flex-col gap-6">
        <h1 class="text-3xl font-bold leading-tight text-balance sm:text-4xl">{{ __("$t.h1") }}</h1>
        <p class="max-w-3xl text-lg text-muted">{{ __("$t.intro") }}</p>

        @include('partials.catalog-filters', ['action' => App\Support\Localized::url('catalog')])
        @include('partials.catalog-table', ['showDomain' => false])
        {{ $sites->links() }}

        <p><a href="{{ route('app.register', ['role' => 'buyer']) }}" class="inline-block rounded bg-brand px-5 py-3 font-semibold text-white hover:bg-brand-dark">{{ __("$t.cta") }}</a></p>
    </div>
@endsection
