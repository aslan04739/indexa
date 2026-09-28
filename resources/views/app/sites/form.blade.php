@extends('layouts.app')
@section('title', $site->exists ? __('Modifier le site') : __('Ajouter un site'))
@section('content')
    <h1 class="text-2xl font-bold">{{ $site->exists ? __('Modifier :domain', ['domain' => $site->domain]) : __('Ajouter un site') }}</h1>
    <form method="POST" action="{{ $site->exists ? route('app.sites.update', $site) : route('app.sites.store') }}" class="flex max-w-2xl flex-col gap-4">
        @csrf
        @if ($site->exists) @method('PUT') @endif
        <x-field name="name" :label="__('Nom du site')" :value="$site->name" required />
        @unless ($site->exists)
            <x-field name="url" type="url" :label="__('Adresse du site')" required placeholder="https://" :hint="__('Page d\'accueil. Vous devrez prouver que le site vous appartient.')" />
        @endunless
        <x-select name="language" :label="__('Langue principale')" :value="$site->language" :options="collect(config('indexa.locales'))->map(fn ($l) => $l['name'])->all()" />
        <x-select name="category" :label="__('Thématique')" :value="$site->category" :options="collect(config('marketplace.categories'))->mapWithKeys(fn ($c) => [$c => __('category.'.$c)])->all()" />
        <x-field name="price_dzd" type="number" :label="__('Votre prix par article (DZD)')" :value="$site->price_dzd" required min="1000" step="100" :hint="__('Montant que vous recevez. Indexa ajoute sa commission au prix affiché à l\'annonceur.')" />
        <x-select name="link_attribute" :label="__('Attribut des liens')" :value="$site->link_attribute" :options="['sponsored' => 'sponsored', 'nofollow' => 'nofollow', 'dofollow' => 'dofollow']" />
        <x-field name="turnaround_days" type="number" :label="__('Délai de publication (jours)')" :value="$site->turnaround_days" required min="1" max="30" />
        <x-field name="monthly_traffic" type="number" :label="__('Visites mensuelles (facultatif)')" :value="$site->monthly_traffic" min="0" />
        <x-field name="description" type="textarea" :label="__('Description pour les annonceurs')" :value="$site->description" rows="3" />
        <x-button>{{ __('Enregistrer') }}</x-button>
    </form>
@endsection
