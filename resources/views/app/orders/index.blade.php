@extends('layouts.app')
@section('title', __('Commandes'))
@section('content')
    <h1 class="text-2xl font-bold">{{ __('Commandes') }}</h1>
    @include('app.orders._table', ['orders' => $orders])
    {{ $orders->links() }}
@endsection
