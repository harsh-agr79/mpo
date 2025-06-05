<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/orders/{order}/export-png', function (Order $order) {
    return view('png.order', compact('order'));
})->name('png.order');
