@extends('layouts.app')
@section('title', __('Nouvelle commande'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Commander un article sur :domain', ['domain' => $site->domain]) }}</h1>

    <dl class="grid gap-3 rounded border border-line bg-white p-4 sm:grid-cols-4">
        <div><dt class="text-sm text-muted">{{ __('Prix') }}</dt><dd class="font-bold tabular-nums">{{ dzd($site->buyerPrice()) }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Type de lien') }}</dt><dd>{{ $site->link_attribute }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Délai de publication') }}</dt><dd>{{ trans_choice(':count jour|:count jours', $site->turnaround_days) }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Votre solde') }}</dt><dd class="tabular-nums">{{ dzd($balance) }}</dd></div>
    </dl>
    @if ($site->description)<p class="max-w-prose text-muted">{{ $site->description }}</p>@endif

    @if ($balance < $site->buyerPrice())
        <p class="rounded border border-amber-600 bg-amber-50 p-4">{{ __('Votre solde est insuffisant pour cette commande.') }} <a class="text-brand underline" href="{{ route('app.wallet') }}">{{ __('Recharger mon portefeuille') }}</a></p>
    @endif

    <form method="POST" action="{{ route('app.orders.store', $site) }}" class="flex max-w-2xl flex-col gap-4">
        @csrf
        <x-field name="target_url" type="url" :label="__('URL cible (votre page)')" required placeholder="https://" />
        <x-field name="anchor_text" :label="__('Texte d\'ancre')" required />
        <x-field name="content" type="textarea" :label="__('Votre article (si vous le fournissez)')" :hint="__('Le texte doit contenir le lien vers l\'URL cible.')" />
        <x-field name="brief" type="textarea" :label="__('Ou un brief pour que l\'éditeur rédige')" :hint="__('Sujet, angle, mots-clés, ton.')" />
        <p class="text-sm text-muted">{{ __('Le montant est prélevé sur votre solde et conservé par Indexa jusqu\'à la validation de la publication. En cas de refus ou de dépassement de délai, il est remboursé.') }}</p>
        <x-button>{{ __('Payer :price et commander', ['price' => dzd($site->buyerPrice())]) }}</x-button>
    </form>
@endsection
