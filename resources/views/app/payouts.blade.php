@extends('layouts.app')
@section('title', __('Retraits'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Retraits') }}</h1>
    <p>{{ __('Solde disponible') }} : <span class="font-bold tabular-nums">{{ dzd($balance) }}</span></p>

    <form method="POST" action="{{ route('app.payouts.store') }}" class="flex max-w-2xl flex-col gap-4 rounded border border-line bg-white p-4">
        @csrf
        <x-field name="amount" type="number" :label="__('Montant (DZD)')" required :min="config('marketplace.min_payout_dzd')" :hint="__('Minimum :min.', ['min' => dzd(config('marketplace.min_payout_dzd'))])" />
        <x-select name="method" :label="__('Moyen de paiement')" :options="['ccp' => 'CCP', 'baridimob' => 'BaridiMob (RIP)', 'bank' => __('Virement bancaire (RIB)')]" />
        <x-field name="account_details" :label="__('Numéro de compte (CCP, RIP ou RIB) et titulaire')" required />
        <x-button>{{ __('Demander le retrait') }}</x-button>
    </form>

    @if ($payouts->isNotEmpty())
        <div class="overflow-x-auto rounded border border-line bg-white">
            <table class="w-full text-sm">
                <thead class="bg-paper text-xs uppercase text-muted"><tr><th class="p-3 text-start">{{ __('Date') }}</th><th class="p-3 text-start">{{ __('Montant') }}</th><th class="p-3 text-start">{{ __('Moyen') }}</th><th class="p-3 text-start">{{ __('Statut') }}</th><th class="p-3 text-start">{{ __('Note') }}</th></tr></thead>
                <tbody>
                    @foreach ($payouts as $payout)
                        <tr class="border-t border-line"><td class="p-3">{{ $payout->created_at->format('d/m/Y') }}</td><td class="p-3 tabular-nums">{{ dzd($payout->amount) }}</td><td class="p-3">{{ strtoupper($payout->method) }}</td><td class="p-3"><x-status :status="$payout->status" /></td><td class="p-3">{{ $payout->admin_note }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
