@extends('layouts.app')
@section('title', __('Tableau de bord'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Bonjour :name', ['name' => auth()->user()->name]) }}</h1>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Solde') }}</div><div class="text-2xl font-bold tabular-nums">{{ dzd($balance) }}</div></div>
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Commandes en cours') }}</div><div class="text-2xl font-bold tabular-nums">{{ $openOrders }}</div></div>
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Commandes terminées') }}</div><div class="text-2xl font-bold tabular-nums">{{ $completedOrders }}</div></div>
    </div>

    @if (auth()->user()->isPublisher() && $sites->isEmpty())
        <p class="rounded border border-line bg-white p-4">{{ __('Ajoutez votre premier site pour recevoir des commandes.') }} <a class="text-brand underline" href="{{ route('app.sites.create') }}">{{ __('Ajouter un site') }}</a></p>
    @endif
    @if (auth()->user()->isBuyer() && $balance === 0)
        <p class="rounded border border-line bg-white p-4">{{ __('Rechargez votre portefeuille par CIB ou Edahabia pour commander.') }} <a class="text-brand underline" href="{{ route('app.wallet') }}">{{ __('Recharger') }}</a></p>
    @endif

    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-bold">{{ auth()->user()->isPublisher() ? __('Commandes à traiter') : __('Publications à valider') }}</h2>
        @include('app.orders._table', ['orders' => $toHandle])
    </section>
@endsection
