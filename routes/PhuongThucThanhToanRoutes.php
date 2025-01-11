<?php

use App\Http\Controllers\Api\PhuongThucThanhToanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('phuong-thuc-thanh-toan', PhuongThucThanhToanController::class);


Route::put('phuong-thuc/trang-thai', [PhuongThucThanhToanController::class, 'updateStatus']);
