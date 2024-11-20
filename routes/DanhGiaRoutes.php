<?php

use App\Http\Controllers\Api\DanhGiaController;
use Illuminate\Support\Facades\Route;

Route::apiResource('danh-gia', DanhGiaController::class)->parameters([
    'danh-gia' => 'danh_gia'
]);
Route::get('/danh-gia/san-pham/{san_pham_id}', [DanhGiaController::class, 'getDanhGiaBySanPhamId']);


