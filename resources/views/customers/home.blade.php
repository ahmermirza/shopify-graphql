@extends('shopify-app::layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{ asset('app/css/styles.css') }}">
@endsection
@section('content')
    <h1>Customers</h1>
    <div id="status"></div>
    <button id="createCustomerButton">Create Customer</button>
    <div id="customers"></div>
@endsection
@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    @include('app.assets.js.customers.scripts')
@endsection
