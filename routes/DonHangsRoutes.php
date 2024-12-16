<?php

use App\Http\Controllers\Api\DonHangsController;
use Illuminate\Support\Facades\Route;

Route::prefix('donhang')->group(function () {
    Route::post('/orders', [DonHangsController::class, 'createOrder']);
    Route::get('/orders/{don_hang_id}', [DonHangsController::class, 'viewOrder']);
    Route::put('/orders/{don_hang_id}/status', [DonHangsController::class, 'updateOrderStatus']);
    Route::post('/orders/{don_hang_id}/payment', [DonHangsController::class, 'processPayment']);
});
