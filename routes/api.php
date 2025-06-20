<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\api_key;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\DamageController;


Route::middleware(api_key::class)->group(function () {
    Route::get('/data', function () {
        return response()->json(['message' => 'Data accessed successfully']);
    });

    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/categories', [ProductController::class, 'getCategories']);
    Route::get('/getinventory', [ProductController::class, 'getInventory']);
    Route::post('/login', [AuthController::class, 'login']);

   Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/user', function (Request $request) {return $request->user();});

        Route::get('/cart', [CartController::class, 'getCart']);
        Route::post('/cartupdate', [CartController::class, 'updateOrRemove']);
        Route::post('/checkout', [CartController::class, 'checkout']);

        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/check-token', [AuthController::class, 'checkToken']);

        Route::get('/resources', [ResourceController::class, 'resources']);
        Route::post('/createdamageticket', [DamageController::class, 'createDamage']);
    });
});
