@extends('layouts.app')
@section('title', __('Catalogue'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Catalogue des sites') }}</h1>
    @include('partials.catalog-filters', ['action' => route('app.catalog')])
    @include('partials.catalog-table', ['showDomain' => true])
    {{ $sites->links() }}
@endsection
