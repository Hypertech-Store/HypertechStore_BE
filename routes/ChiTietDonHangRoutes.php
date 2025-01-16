<?php

use App\Http\Controllers\Api\ChiTietDonHangController;
use Illuminate\Support\Facades\Route;

Route::apiResource('chi-tiet-don-hang', ChiTietDonHangController::class);

