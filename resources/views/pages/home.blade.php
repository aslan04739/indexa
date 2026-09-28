@extends('layouts.public')

@section('content')
    @php($t = 'site.pages.home')

    <section class="flex flex-col gap-5 pb-12">
        <h1 class="max-w-3xl text-3xl font-bold leading-tight text-balance sm:text-5xl">{{ __("$t.h1") }}</h1>
        <p class="max-w-2xl text-lg text-muted">{{ __("$t.intro") }}</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ App\Support\Localized::url('advertisers') }}" class="rounded bg-brand px-5 py-3 font-semibold text-white hover:bg-brand-dark">{{ __('site.cta_advertiser') }}</a>
            <a href="{{ App\Support\Localized::url('publishers') }}" class="rounded border border-ink px-5 py-3 font-semibold hover:border-brand hover:text-brand">{{ __('site.cta_publisher') }}</a>
        </div>
    </section>

    <section class="flex flex-col gap-6 border-t border-line py-12">
        <h2 class="text-2xl font-bold">{{ __("$t.steps_title") }}</h2>
        <ol class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__("$t.steps") as $i => [$title, $text])
                <li class="flex flex-col gap-2">
                    <span class="font-mono text-sm text-brand">{{ $i + 1 }}</span>
                    <h3 class="font-semibold">{{ $title }}</h3>
                    <p class="text-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="flex flex-col gap-6 border-t border-line py-12">
        <h2 class="text-2xl font-bold">{{ __("$t.why_title") }}</h2>
        <ul class="grid gap-6 sm:grid-cols-2">
            @foreach (__("$t.why") as [$title, $text])
                <li class="flex flex-col gap-1">
                    <h3 class="font-semibold">{{ $title }}</h3>
                    <p class="text-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="flex flex-col gap-4 border-t border-line py-12">
        <h2 class="text-2xl font-bold">{{ __("$t.faq_title") }}</h2>
        @foreach (__("$t.faq") as [$q, $a])
            {{-- Native <details>: collapsible without JavaScript, answer text is in the HTML. --}}
            <details class="border-b border-line py-3">
                <summary class="cursor-pointer font-semibold">{{ $q }}</summary>
                <p class="pt-2 text-muted">{{ $a }}</p>
            </details>
        @endforeach
    </section>
@endsection
