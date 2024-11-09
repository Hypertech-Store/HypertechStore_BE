<?php

use App\Http\Controllers\Api\KhachHangController;
use Illuminate\Support\Facades\Route;

Route::prefix('khach-hang')->group(function () {

    Route::post('/login', [KhachHangController::class, 'login']);

    Route::post('/register', [KhachHangController::class, 'register']);

    Route::post('/logout', [KhachHangController::class, 'logout']);


});

