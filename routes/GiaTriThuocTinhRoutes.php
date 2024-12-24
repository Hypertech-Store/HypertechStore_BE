<?php

use App\Http\Controllers\Api\GiaTriThuocTinhController;
use Illuminate\Support\Facades\Route;

Route::apiResource('gia-tri-thuoc-tinh', GiaTriThuocTinhController::class);
Route::get('lay-gia-tri-theo-thuoc-tinh', [GiaTriThuocTinhController::class, 'layGiaTriThuocTinhTheoThuocTinh']);
