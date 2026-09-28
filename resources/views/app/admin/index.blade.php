@extends('layouts.app')
@section('title', __('Administration'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Administration') }}</h1>

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Volume validé') }}</div><div class="text-xl font-bold tabular-nums">{{ dzd($stats['gmv']) }}</div></div>
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Commissions') }}</div><div class="text-xl font-bold tabular-nums">{{ dzd($stats['commission']) }}</div></div>
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Sites au catalogue') }}</div><div class="text-xl font-bold tabular-nums">{{ $stats['sites'] }}</div></div>
        <div class="rounded border border-line bg-white p-4"><div class="text-sm text-muted">{{ __('Commandes en cours') }}</div><div class="text-xl font-bold tabular-nums">{{ $stats['open_orders'] }}</div></div>
    </div>

    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-bold">{{ __('Sites à examiner') }} ({{ $pendingSites->count() }})</h2>
        @foreach ($pendingSites as $site)
            <div class="flex flex-col gap-3 rounded border border-line bg-white p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a class="font-semibold text-brand underline" href="{{ $site->url }}" target="_blank" rel="noopener nofollow">{{ $site->domain }}</a>
                    <span class="text-sm text-muted">{{ $site->user->name }} · {{ strtoupper($site->language) }} · {{ __('category.'.$site->category) }} · {{ dzd($site->price_dzd) }} · {{ $site->link_attribute }}</span>
                    <x-status :status="$site->verified_at ? 'verified' : 'unverified'" />
                </div>
                <div class="flex flex-wrap items-end gap-3">
                    @if ($site->verified_at)
                        <form method="POST" action="{{ route('app.admin.sites.approve', $site) }}" class="flex items-end gap-2">@csrf
                            <x-field name="domain_rating" type="number" label="DR" min="0" max="100" class="w-20" />
                            <x-button>{{ __('Approuver') }}</x-button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('app.admin.sites.reject', $site) }}" class="flex items-end gap-2">@csrf
                        <x-field name="rejection_reason" :label="__('Motif')" />
                        <x-button variant="danger">{{ __('Refuser') }}</x-button>
                    </form>
                </div>
            </div>
        @endforeach
    </section>

    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-bold">{{ __('Litiges') }} ({{ $disputes->count() }})</h2>
        @foreach ($disputes as $order)
            <div class="flex flex-col gap-2 rounded border border-line bg-white p-4">
                <a class="font-semibold text-brand underline" href="{{ route('app.orders.show', $order) }}">#{{ $order->id }} · {{ $order->site->domain }} · {{ dzd($order->buyer_price) }}</a>
                <p>{{ $order->dispute_reason }}</p>
                <form method="POST" action="{{ route('app.admin.orders.resolve', $order) }}" class="flex gap-2">@csrf
                    <x-button name="decision" value="complete">{{ __('Payer l\'éditeur') }}</x-button>
                    <x-button name="decision" value="refund" variant="danger">{{ __('Rembourser l\'annonceur') }}</x-button>
                </form>
            </div>
        @endforeach
    </section>

    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-bold">{{ __('Retraits à payer') }} ({{ $payouts->count() }})</h2>
        @foreach ($payouts as $payout)
            <div class="flex flex-col gap-2 rounded border border-line bg-white p-4">
                <p><span class="font-semibold tabular-nums">{{ dzd($payout->amount) }}</span> · {{ $payout->user->name }} · {{ strtoupper($payout->method) }} · <span dir="ltr">{{ $payout->account_details }}</span></p>
                <div class="flex flex-wrap items-end gap-3">
                    <form method="POST" action="{{ route('app.admin.payouts.paid', $payout) }}" class="flex items-end gap-2">@csrf
                        <x-field name="admin_note" :label="__('Référence du virement')" />
                        <x-button>{{ __('Marquer payé') }}</x-button>
                    </form>
                    <form method="POST" action="{{ route('app.admin.payouts.reject', $payout) }}" class="flex items-end gap-2">@csrf
                        <x-field name="admin_note" :label="__('Motif')" />
                        <x-button variant="danger">{{ __('Refuser') }}</x-button>
                    </form>
                </div>
            </div>
        @endforeach
    </section>
@endsection
