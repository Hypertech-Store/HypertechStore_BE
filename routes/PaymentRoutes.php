<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;


Route::prefix('thanh-toan')->group(function () {
    Route::post('/vppay/create', [PaymentController::class, 'createPayment']);
    Route::get('/vppay/callback', [PaymentController::class, 'handleCallback']);
});
