<?php

use App\Http\Controllers\Api\BienTheSanPhamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('bien-the-san-pham', BienTheSanPhamController::class);
Route::get('/san-pham/{san_pham_id}/bien-the', [BienTheSanPhamController::class, 'getBienTheBySanPhamId']);
Route::post('bien-the-san-pham/kiem-tra-bien-the', [BienTheSanPhamController::class, 'getBienTheByAttributes']);
