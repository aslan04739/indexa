@extends('layouts.app')
@section('title', __('Mes sites'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ __('Mes sites') }}</h1>
        <a class="rounded bg-brand px-4 py-2 font-semibold text-white" href="{{ route('app.sites.create') }}">{{ __('Ajouter un site') }}</a>
    </div>
    @forelse ($sites as $site)
        <a href="{{ route('app.sites.show', $site) }}" class="flex flex-wrap items-center justify-between gap-3 rounded border border-line bg-white p-4 hover:border-brand">
            <span><span class="font-semibold">{{ $site->domain }}</span> · {{ dzd($site->price_dzd) }}</span>
            <span class="flex gap-2">
                @unless ($site->verified_at)<x-status status="unverified" />@endunless
                <x-status :status="$site->status" />
            </span>
        </a>
    @empty
        <p class="text-muted">{{ __('Aucun site pour l\'instant.') }}</p>
    @endforelse
@endsection
