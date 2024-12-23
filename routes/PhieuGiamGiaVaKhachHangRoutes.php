<?php

use App\Http\Controllers\Api\PhieuGiamGiaVaKhachHangController;
use Illuminate\Support\Facades\Route;

Route::resource('phieu-giam-gia-khach-hang', PhieuGiamGiaVaKhachHangController::class);
