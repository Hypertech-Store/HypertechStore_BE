<?php

use App\Http\Controllers\Api\ThuocTinhSanPhamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('thuoc-tinh-san-pham', ThuocTinhSanPhamController::class);
Route::get('thuoc-tinh/gia-tri-theo-thuoc-tinh', [ThuocTinhSanPhamController::class, 'layGiaTriThuocTinhTheoThuocTinh']);
