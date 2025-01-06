<?php

use App\Http\Controllers\Api\TrangThaiDonHangController;
use Illuminate\Support\Facades\Route;

Route::apiResource('trang-thai-don-hang', TrangThaiDonHangController::class);
Route::get('/trang-thai-don-hang/getAllTrangThaiDonHang', [TrangThaiDonHangController::class, 'getAll']);
