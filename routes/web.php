<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIChatController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/orders/{order}/export-png', function (Order $order) {
    return view('png.order', compact('order'));
})->name('png.order');

Route::get('/orders/{order}/export-png-with-image', function (Order $order) {
    return view('png.orderImg', compact('order'));
})->name('png.orderImg');

Route::post('/ai-chat', [AIChatController::class, 'reply']);

Route::post('/ai-chat-stream', [\App\Http\Controllers\AIChatController::class, 'stream']);