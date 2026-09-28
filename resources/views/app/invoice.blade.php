@php($legal = config('indexa.legal'))
@extends('layouts.app')
@section('title', __('Facture :number', ['number' => $payment->invoiceNumber()]))
@section('content')
    <article class="mx-auto flex w-full max-w-3xl flex-col gap-6 rounded border border-line bg-white p-8">
        <header class="flex flex-wrap justify-between gap-6">
            <div>
                <p class="text-xl font-bold">{{ $legal['name'] }}</p>
                @if ($legal['address'])<p>{{ $legal['address'] }}</p>@endif
                @if ($legal['rc'])<p>RC : <span dir="ltr">{{ $legal['rc'] }}</span></p>@endif
                @if ($legal['nif'])<p>NIF : <span dir="ltr">{{ $legal['nif'] }}</span></p>@endif
                @if ($legal['nis'])<p>NIS : <span dir="ltr">{{ $legal['nis'] }}</span></p>@endif
                <p>{{ config('indexa.contact_email') }}</p>
            </div>
            <div class="text-end">
                <h1 class="text-2xl font-bold">{{ __('Facture') }}</h1>
                <p dir="ltr">{{ $payment->invoiceNumber() }}</p>
                <p>{{ $payment->paid_at->format('d/m/Y') }}</p>
            </div>
        </header>

        <section>
            <h2 class="text-sm font-semibold uppercase text-muted">{{ __('Client') }}</h2>
            <p class="font-semibold">{{ $user->company ?: $user->name }}</p>
            @if ($user->company)<p>{{ $user->name }}</p>@endif
            <p>{{ $user->email }}</p>
        </section>

        <table class="w-full text-sm">
            <thead class="border-b border-line text-xs uppercase text-muted"><tr><th class="py-2 text-start">{{ __('Désignation') }}</th><th class="py-2 text-end">{{ __('Montant') }}</th></tr></thead>
            <tbody>
                <tr class="border-b border-line"><td class="py-3">{{ __('Rechargement du portefeuille Indexa') }}</td><td class="py-3 text-end tabular-nums">{{ dzd($payment->amount) }}</td></tr>
            </tbody>
            <tfoot><tr><td class="py-3 font-bold">{{ __('Total payé') }}</td><td class="py-3 text-end font-bold tabular-nums">{{ dzd($payment->amount) }}</td></tr></tfoot>
        </table>

        <p class="text-sm text-muted">{{ __('Payé par carte CIB ou Edahabia via Chargily Pay. Référence : :ref', ['ref' => $payment->provider_checkout_id ?? $payment->id]) }}</p>
    </article>
@endsection
