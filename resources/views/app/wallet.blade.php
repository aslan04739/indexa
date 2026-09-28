@extends('layouts.app')
@section('title', __('Portefeuille'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Portefeuille') }}</h1>
    <p class="text-3xl font-bold tabular-nums">{{ dzd($balance) }}</p>

    @if (auth()->user()->isBuyer())
        <form method="POST" action="{{ route('app.wallet.topup') }}" class="flex flex-wrap items-end gap-3 rounded border border-line bg-white p-4">
            @csrf
            <x-field name="amount" type="number" :label="__('Montant à recharger (DZD)')" required :min="config('marketplace.min_topup_dzd')" step="500" value="10000" />
            <x-button>{{ __('Payer par CIB ou Edahabia') }}</x-button>
            <p class="w-full text-sm text-muted">{{ __('Paiement sécurisé par Chargily Pay. Une facture est émise pour chaque rechargement.') }}</p>
        </form>
    @endif

    <section class="flex flex-col gap-3">
        <h2 class="text-lg font-bold">{{ __('Historique') }}</h2>
        @if ($transactions->isEmpty())
            <p class="text-muted">{{ __('Aucune opération.') }}</p>
        @else
            <div class="overflow-x-auto rounded border border-line bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-paper text-xs uppercase text-muted"><tr><th class="p-3 text-start">{{ __('Date') }}</th><th class="p-3 text-start">{{ __('Opération') }}</th><th class="p-3 text-start">{{ __('Détail') }}</th><th class="p-3 text-end">{{ __('Montant') }}</th></tr></thead>
                    <tbody>
                        @foreach ($transactions as $tx)
                            <tr class="border-t border-line">
                                <td class="p-3 tabular-nums">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3">{{ __('tx.'.$tx->type) }}</td>
                                <td class="p-3">
                                    {{ $tx->description }}
                                    @if ($tx->reference instanceof App\Models\Payment && $tx->reference->status === 'paid')
                                        · <a class="text-brand underline" href="{{ route('app.wallet.invoice', $tx->reference) }}">{{ __('Facture') }}</a>
                                    @endif
                                </td>
                                <td @class(['p-3 text-end tabular-nums font-semibold', 'text-brand' => $tx->amount > 0])>{{ $tx->amount > 0 ? '+' : '' }}{{ dzd($tx->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $transactions->links() }}
        @endif
    </section>
@endsection
