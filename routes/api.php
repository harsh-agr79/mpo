<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\api_key;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\AnalyticsController;


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

        //Cart Routes
        Route::get('/cart', [CartController::class, 'getCart']);
        Route::post('/cartupdate', [CartController::class, 'updateOrRemove']);
        Route::post('/checkout', [CartController::class, 'checkout']);

        //Order Routes
        Route::get('/confirmedorders', [CartController::class, 'getConfirmedOrders']);
        Route::get('/savedorders', [CartController::class, 'getSavedOrders']);
        Route::get('/orderdetails/{id}', [CartController::class, 'getOrderDetails']);
        
        //Auth Routes
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/check-token', [AuthController::class, 'checkToken']);

        //Resource Routes
        Route::get('/resources', [ResourceController::class, 'resources']);
        Route::get('/download/{id}', [ResourceController::class, 'download']);
        //
        Route::post('/createdamageticket', [DamageController::class, 'createDamage']);
        Route::get('/damagetickets', [DamageController::class, 'getDamageTickets']);
        Route::get('/damageticket/{id}', [DamageController::class, 'getDamageTicket']);

        Route::get('/mainanalytics', [AnalyticsController::class, 'mainAnalytics']);
    });
});
