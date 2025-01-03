<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['verify.shopify']], function () {
    // products routes
    Route::get('products/{cursor?}', [HomeController::class, 'products'])->name('products');
    Route::get('product/query', [HomeController::class, 'query'])->name('product.query');
    Route::post('create', [HomeController::class, 'create'])->name('create');
    Route::post('create-product-variant', [HomeController::class, 'insertVariants'])->name('product.variants.insert');
    Route::patch('product-update', [HomeController::class, 'update'])->name('product.update');
    Route::get('product/show', [HomeController::class, 'showList'])->name('product.show');
    Route::post('product/options/values', [HomeController::class, 'updateProductOptionValues'])->name('product.option.values.insert');
    Route::get('product/options/values', [HomeController::class, 'showProductOptions'])->name('product.options.show');
    Route::get('product/variants', [HomeController::class, 'showProductVariants'])->name('product.variants.show');
    Route::get('product/variants-update', [HomeController::class, 'updateProductVariants'])->name('product.variants.update');
    Route::get('product/variants-delete', [HomeController::class, 'deleteProductVariants'])->name('product.variants.delete');
    Route::get('locations', [HomeController::class, 'showLocations'])->name('locations');
    Route::get('inventory-items', [HomeController::class, 'showInventoryItems'])->name('inventory-items');
    Route::get('inventory-items/quantity', [HomeController::class, 'setInventoryItemsQuantity'])->name('set.inventory-items.quantity');

    // cutomers routes
    Route::get('customers/{cursor?}', [CustomerController::class, 'customers'])->name('customers');
    Route::post('customer-create', [CustomerController::class, 'create'])->name('customer.create');
    Route::patch('customer-update', [CustomerController::class, 'update'])->name('customer.update');

    // orders routes
    Route::post('order-create', [OrderController::class, 'create'])->name('order.create');
    Route::post('order-update', [OrderController::class, 'updateOrder'])->name('order.update');
});
