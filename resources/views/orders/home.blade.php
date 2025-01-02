@extends('shopify-app::layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{ asset('app/css/styles.css') }}">
@endsection
@section('content')
    <h1>Orders</h1>
    <div id="create-status"></div>
    <button id="createOrderButton">Create Draft Order</button><br><br>
    <div id="status"></div>
    <button id="closeOrderButton" style="display: none;">Close Order</button>
@endsection
@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    @include('app.assets.js.orders.scripts')
@endsection
