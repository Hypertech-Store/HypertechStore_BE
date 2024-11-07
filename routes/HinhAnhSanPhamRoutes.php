<?php

use App\Http\Controllers\Api\HinhAnhSanPhamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('hinh-anh-san-pham', HinhAnhSanPhamController::class);
