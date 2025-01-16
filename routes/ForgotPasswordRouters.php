<?php

use App\Http\Controllers\Api\KhachHangController;
use Illuminate\Support\Facades\Route;

Route::prefix('reset')->group(function () {
    Route::post('/quen-mat-khau', [KhachHangController::class, 'quenMatKhau']);
    Route::post('/dat-lai-mat-khau', [KhachHangController::class, 'datLaiMatKhau']);
});
