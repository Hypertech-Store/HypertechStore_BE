<?php

use App\Http\Controllers\Api\SanPhamVaThongSoController;
use App\Models\SanPhamVaThongSo;
use Illuminate\Support\Facades\Route;

Route::apiResource('san-pham-va-thong-so', SanPhamVaThongSoController::class);
