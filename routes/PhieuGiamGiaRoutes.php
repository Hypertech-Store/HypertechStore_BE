<?php

use App\Http\Controllers\Api\PhieuGiamGiaController;
use Illuminate\Support\Facades\Route;

Route::apiResource('phieu-giam-gia', PhieuGiamGiaController::class);
Route::post('phieu-giam-gia/phieu-giam-gia-phu-hop', [PhieuGiamGiaController::class, 'layPhieuGiamGiaPhuHopVoiDonHang']);
