@extends('shopify-app::layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{ asset('app/css/styles.css') }}">
@endsection
@section('content')
    <h1>Products</h1>
    <div id="status"></div>
    <button id="createProductButton">Create Product</button><br><br>
    <div id="update-status"></div>
    <button id="updateProductButton" style="display: none;">Update Product</button>
    <div id="tempProductId" style="display: none;"></div>
    <div id="products"></div>
@endsection
@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    @include('app.assets.js.scripts')
@endsection
