@extends('layouts.app')
@section('title', __('Connexion'))
@section('content')
    <form method="POST" action="{{ route('app.login') }}" class="mx-auto flex w-full max-w-md flex-col gap-4">
        @csrf
        <h1 class="text-2xl font-bold">{{ __('Connexion') }}</h1>
        <x-field name="email" type="email" :label="__('Email')" required autocomplete="email" />
        <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="current-password" />
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> {{ __('Rester connecté') }}</label>
        <x-button>{{ __('Se connecter') }}</x-button>
        <p class="text-sm">{{ __('Pas encore de compte ?') }} <a class="text-brand underline" href="{{ route('app.register') }}">{{ __('Créer un compte') }}</a></p>
    </form>
@endsection
