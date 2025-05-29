<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\api_key;
use App\Http\Controllers\ProductController;


Route::middleware(api_key::class)->group(function () {
    Route::get('/data', function () {
        return response()->json(['message' => 'Data accessed successfully']);
    });

    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/categories', [ProductController::class, 'getCategories']);
    Route::get('/getinventory', [ProductController::class, 'getInventory']);

   Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/user', function (Request $request) {return $request->user();});



    });
});
