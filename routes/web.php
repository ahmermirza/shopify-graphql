<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['verify.shopify']], function () {
    Route::get('/', [HomeController::class, 'showProductVariants'])->name('home');

    // Route::get('/', [CustomerController::class, 'home'])->name('home');

    // Route::get('/', [OrderController::class, 'home'])->name('home');
});
