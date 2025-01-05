<?php

use App\Http\Controllers\Api\ThongKeController;
use Illuminate\Support\Facades\Route;

Route::get('/thong-ke', [ThongKeController::class, 'thongKe']);
Route::get('thong-ke-san-pham', [ThongKeController::class, 'thongKeSanPham']);
Route::get('/thong-ke-don-hang-7-ngay', [ThongKeController::class, 'thongKeDonHang7Ngay']);
Route::get('/thong-ke-khach-hang-moi-7-ngay', [ThongKeController::class, 'thongKeKhachHangMoi7Ngay']);
