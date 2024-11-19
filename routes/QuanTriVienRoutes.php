<?php

use App\Http\Controllers\Api\QuanTriVienController;
use Illuminate\Support\Facades\Route;


Route::prefix('quan-tri-viens')->group(function () {
    Route::post('/add', [QuanTriVienController::class, 'store']); // Thêm mới
    Route::put('/update/{id}', [QuanTriVienController::class, 'update']); // Sửa
    Route::delete('/delete/{id}', [QuanTriVienController::class, 'destroy']); // Xóa
    Route::patch('/{id}/toggle-active', [QuanTriVienController::class, 'toggleActive']); // Bật/tắt
    Route::get('/getAll', [QuanTriVienController::class, 'index']); // Lấy danh sách
    Route::get('/detail/{id}', [QuanTriVienController::class, 'show']); // Lấy chi tiết
    Route::post('/login', [QuanTriVienController::class, 'login']);
});
