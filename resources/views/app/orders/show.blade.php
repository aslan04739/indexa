@extends('layouts.app')
@section('title', __('Commande #:id', ['id' => $order->id]))
@php($user = auth()->user())
@section('content')
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold">{{ __('Commande #:id', ['id' => $order->id]) }}</h1>
        <x-status :status="$order->status" />
    </div>

    <dl class="grid gap-4 rounded border border-line bg-white p-4 sm:grid-cols-2">
        <div><dt class="text-sm text-muted">{{ __('Site') }}</dt><dd class="font-semibold">{{ $order->site->domain }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Prix') }}</dt><dd class="tabular-nums">{{ dzd($user->isPublisher() ? $order->publisher_price : $order->buyer_price) }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('URL cible') }}</dt><dd class="break-all">{{ $order->target_url }}</dd></div>
        <div><dt class="text-sm text-muted">{{ __('Texte d\'ancre') }}</dt><dd>{{ $order->anchor_text }}</dd></div>
        @if ($order->deadline_at)
            <div><dt class="text-sm text-muted">{{ __('Échéance') }}</dt><dd>{{ $order->deadline_at->format('d/m/Y H:i') }}</dd></div>
        @endif
        @if ($order->published_url)
            <div><dt class="text-sm text-muted">{{ __('Article publié') }}</dt><dd class="break-all"><a class="text-brand underline" href="{{ $order->published_url }}" rel="nofollow noopener" target="_blank">{{ $order->published_url }}</a></dd></div>
        @endif
        @if ($order->link_status)
            <div>
                <dt class="text-sm text-muted">{{ __('Dernière vérification du lien') }}</dt>
                <dd class="flex flex-wrap items-center gap-2"><x-status :status="$order->link_status" /> <span class="text-sm">rel="{{ $order->link_rel }}" · {{ $order->last_checked_at?->format('d/m/Y H:i') }}</span></dd>
            </div>
        @endif
        @if ($order->monitor_until)
            <div><dt class="text-sm text-muted">{{ __('Surveillance jusqu\'au') }}</dt><dd>{{ $order->monitor_until->format('d/m/Y') }}</dd></div>
        @endif
        @if ($order->refusal_reason)
            <div><dt class="text-sm text-muted">{{ __('Motif du refus') }}</dt><dd>{{ $order->refusal_reason }}</dd></div>
        @endif
        @if ($order->dispute_reason)
            <div class="sm:col-span-2"><dt class="text-sm text-muted">{{ __('Motif du litige') }}</dt><dd>{{ $order->dispute_reason }}</dd></div>
        @endif
    </dl>

    @if ($order->content)
        <section class="flex flex-col gap-2"><h2 class="font-bold">{{ __('Article fourni') }}</h2><pre class="whitespace-pre-wrap rounded border border-line bg-white p-4 font-sans">{{ $order->content }}</pre></section>
    @endif
    @if ($order->brief)
        <section class="flex flex-col gap-2"><h2 class="font-bold">{{ __('Brief') }}</h2><p class="whitespace-pre-wrap rounded border border-line bg-white p-4">{{ $order->brief }}</p></section>
    @endif

    {{-- Publisher actions --}}
    @if ($user->id === $order->publisher_id)
        @if ($order->status === 'pending')
            <div class="flex flex-wrap items-end gap-3">
                <form method="POST" action="{{ route('app.orders.accept', $order) }}">@csrf<x-button>{{ __('Accepter') }}</x-button></form>
                <form method="POST" action="{{ route('app.orders.refuse', $order) }}" class="flex flex-wrap items-end gap-2">@csrf
                    <x-field name="refusal_reason" :label="__('Motif (facultatif)')" />
                    <x-button variant="danger">{{ __('Refuser') }}</x-button>
                </form>
            </div>
        @elseif ($order->status === 'accepted')
            <form method="POST" action="{{ route('app.orders.publish', $order) }}" class="flex max-w-2xl flex-col gap-3">@csrf
                <x-field name="published_url" type="url" :label="__('URL de l\'article publié')" required placeholder="https://" :hint="__('Indexa vérifie que la page est en ligne, indexable et contient le lien vers l\'URL cible.')" />
                <x-button>{{ __('Soumettre la publication') }}</x-button>
            </form>
            <form method="POST" action="{{ route('app.orders.refuse', $order) }}">@csrf<x-button variant="danger">{{ __('Annuler et rembourser l\'annonceur') }}</x-button></form>
        @endif
    @endif

    {{-- Buyer actions --}}
    @if ($user->id === $order->buyer_id && $order->status === 'published')
        <div class="flex flex-col gap-4">
            <p>{{ __('Vérifiez l\'article. Sans action de votre part, la commande sera validée automatiquement le :date.', ['date' => $order->deadline_at?->format('d/m/Y')]) }}</p>
            <form method="POST" action="{{ route('app.orders.validate', $order) }}">@csrf<x-button>{{ __('Valider la publication') }}</x-button></form>
            <form method="POST" action="{{ route('app.orders.dispute', $order) }}" class="flex max-w-2xl flex-col gap-2">@csrf
                <x-field name="dispute_reason" type="textarea" :label="__('Signaler un problème')" rows="3" />
                <x-button variant="danger">{{ __('Ouvrir un litige') }}</x-button>
            </form>
        </div>
    @endif

    @if ($order->published_url && in_array($order->status, ['published', 'completed', 'disputed']))
        <form method="POST" action="{{ route('app.orders.recheck', $order) }}">@csrf<x-button variant="secondary">{{ __('Revérifier le lien maintenant') }}</x-button></form>
    @endif
@endsection
