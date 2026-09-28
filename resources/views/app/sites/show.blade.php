@extends('layouts.app')
@section('title', $site->domain)
@section('content')
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold">{{ $site->domain }}</h1>
        <x-status :status="$site->status" />
        <a class="text-brand underline" href="{{ route('app.sites.edit', $site) }}">{{ __('Modifier') }}</a>
    </div>

    @if ($site->status === 'rejected' && $site->rejection_reason)
        <p class="rounded border border-red-700 bg-red-50 p-4">{{ __('Motif du refus') }} : {{ $site->rejection_reason }}</p>
    @endif

    @if (! $site->verified_at)
        <section class="flex max-w-3xl flex-col gap-3 rounded border border-line bg-white p-4">
            <h2 class="font-bold">{{ __('Étape 1 : prouvez que le site vous appartient') }}</h2>
            <p>{{ __('Ajoutez cette balise dans la section <head> de la page d\'accueil de :url, puis cliquez sur Vérifier.', ['url' => $site->url]) }}</p>
            <pre class="overflow-x-auto rounded bg-paper p-3 text-sm" dir="ltr">{{ $site->verificationMetaTag() }}</pre>
            <form method="POST" action="{{ route('app.sites.verify', $site) }}">@csrf<x-button>{{ __('Vérifier') }}</x-button></form>
        </section>
    @elseif ($site->status === 'pending')
        <p class="rounded border border-line bg-white p-4">{{ __('Propriété vérifiée. L\'équipe Indexa examine votre site avant de le publier dans le catalogue.') }}</p>
    @endif

    <dl class="grid gap-3 rounded border border-line bg-white p-4 sm:grid-cols-3">
        <div><dt class="text-sm text-muted">{{ __('Votre prix') }}</dt><dd class="tabular-nums">{{ dzd($site->price_dzd) }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Prix affiché aux annonceurs') }}</dt><dd class="tabular-nums">{{ dzd($site->buyerPrice()) }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Attribut des liens') }}</dt><dd>{{ $site->link_attribute }}</dd></div>
    </dl>
@endsection
