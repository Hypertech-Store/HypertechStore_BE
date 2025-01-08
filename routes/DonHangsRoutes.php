<?php

use App\Http\Controllers\Api\DonHangsController;
use Illuminate\Support\Facades\Route;

Route::prefix('donhang')->group(function () {
    Route::post('/orders', [DonHangsController::class, 'createOrder']);
    Route::get('/orders/{khach_hang_id}', [DonHangsController::class, 'viewOrder']);
    Route::get('/order-details/{orderId}', [DonHangsController::class, 'orderDetails']);


    Route::put('/orders/{don_hang_id}/status', [DonHangsController::class, 'updateOrderStatus']);
    Route::post('/orders/{don_hang_id}/payment', [DonHangsController::class, 'processPayment']);
});
