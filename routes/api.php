<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cart\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware(['throttle:120,1'])->group(function () {
    Route::post('login',                        [LoginController::class, 'login']);

    Route::post('register',                     [RegisterController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::post('addProductInCart',         [CartController::class, 'add']);

        Route::post('setCartProductQuantity',   [CartController::class, 'set']);

        Route::post('removeProductFromCart',    [CartController::class, 'remove']);

        Route::get('getUserCart',               [CartController::class, 'get']);
    });
});
