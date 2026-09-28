@extends('layouts.app')
@section('title', __('Créer un compte'))
@section('content')
    <form method="POST" action="{{ route('app.register') }}" class="mx-auto flex w-full max-w-md flex-col gap-4">
        @csrf
        <h1 class="text-2xl font-bold">{{ __('Créer un compte') }}</h1>
        <fieldset class="flex flex-col gap-2">
            <legend class="text-sm font-semibold">{{ __('Je suis') }}</legend>
            <label class="flex items-center gap-2"><input type="radio" name="role" value="buyer" @checked(old('role', $role) === 'buyer')> {{ __('Annonceur : je veux publier des articles sponsorisés') }}</label>
            <label class="flex items-center gap-2"><input type="radio" name="role" value="publisher" @checked(old('role', $role) === 'publisher')> {{ __('Éditeur : je veux monétiser mon site') }}</label>
        </fieldset>
        <x-field name="name" :label="__('Nom complet')" required autocomplete="name" />
        <x-field name="company" :label="__('Entreprise (facultatif)')" autocomplete="organization" />
        <x-field name="phone" type="tel" :label="__('Téléphone (facultatif)')" autocomplete="tel" />
        <x-field name="email" type="email" :label="__('Email')" required autocomplete="email" />
        <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="new-password" :hint="__('8 caractères minimum.')" />
        <x-field name="password_confirmation" type="password" :label="__('Confirmer le mot de passe')" required autocomplete="new-password" />
        <x-button>{{ __('Créer mon compte') }}</x-button>
        <p class="text-sm">{{ __('Déjà inscrit ?') }} <a class="text-brand underline" href="{{ route('app.login') }}">{{ __('Se connecter') }}</a></p>
    </form>
@endsection
