<?php

use App\Http\Controllers\Api\KhachHangController;
use Illuminate\Support\Facades\Route;

Route::prefix('khach-hang')->group(function () {

    Route::post('/login', [KhachHangController::class, 'login']);

    Route::post('/register', [KhachHangController::class, 'register']);

    Route::post('/logout', [KhachHangController::class, 'logout']);

    Route::get('/profile/{id}', [KhachHangController::class, 'show']); // API lấy thông tin người dùng

    Route::put('/update-profile/{id}', [KhachHangController::class, 'update']); // API cập nhật thông tin người dùng
    Route::put('/trang-thai', [KhachHangController::class, 'updateStatus']);


    Route::get('/tai-khoan', [KhachHangController::class, 'getAllUsers']);
});

Route::get('/get-all-khach-hang', [KhachHangController::class, 'getAllKhachHang']);



