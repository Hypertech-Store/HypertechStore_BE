<?php

use App\Http\Controllers\Api\ThuocTinhSanPhamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('thuoc-tinh-san-pham', ThuocTinhSanPhamController::class);
