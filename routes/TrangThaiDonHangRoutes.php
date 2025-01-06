<?php

use App\Http\Controllers\Api\TrangThaiDonHangController;
use Illuminate\Support\Facades\Route;

Route::apiResource('trang-thai-don-hang', TrangThaiDonHangController::class);
