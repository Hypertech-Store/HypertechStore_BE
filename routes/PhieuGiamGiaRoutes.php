<?php

use App\Http\Controllers\Api\PhieuGiamGiaController;
use Illuminate\Support\Facades\Route;

Route::prefix('phieu-giam-gia')->group(function () {

    Route::get('/{phieu-giam-gia-id}', [PhieuGiamGiaController::class, 'index']);

    Route::post('/create', [PhieuGiamGiaController::class, 'store']);

    Route::get('/detail/{id}', [PhieuGiamGiaController::class, 'show']);

    Route::put('/update/{id}', [PhieuGiamGiaController::class, 'update']);

    Route::delete('/delete/{id}', [PhieuGiamGiaController::class, 'destroy']);

    Route::post('/phieu-giam-gia-phu-hop', [PhieuGiamGiaController::class, 'layPhieuGiamGiaPhuHopVoiDonHang']);

    Route::post('/check-phieu-giam-gia', [PhieuGiamGiaController::class, 'checkPhieuGiamGia']);

});
