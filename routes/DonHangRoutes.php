<?php

use App\Http\Controllers\Api\DonHangController;
use Illuminate\Support\Facades\Route;

Route::prefix('don-hang')->group(function () {
    Route::get('/{khachHangId}', [DonHangController::class, 'index']);
    Route::post('/create', [DonHangController::class, 'store']);
    Route::get('/detail/{id}', [DonHangController::class, 'show']);
    Route::put('/update/{id}', [DonHangController::class, 'update']);
    Route::delete('/delete/{id}', [DonHangController::class, 'destroy']);
    Route::post('/checkout', [DonHangController::class, 'checkout']);
});

