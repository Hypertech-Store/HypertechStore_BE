
<?php

use App\Http\Controllers\Api\HinhThucVanChuyenController;
use Illuminate\Support\Facades\Route;

Route::apiResource('hinh-thuc-van-chuyen', HinhThucVanChuyenController::class);

Route::get('/get-all-hinh-thuc-van-chuyen', [HinhThucVanChuyenController::class, 'allHinhThucVanChuyen']);
