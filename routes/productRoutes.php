<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SanPhamController;

Route::prefix('san-pham')->group(function () {
    Route::get('/allProduct', [SanPhamController::class, 'getAllProduct']);
    Route::post('/create', [SanPhamController::class, 'createProduct']);
    Route::get('/detail/{id}', [SanPhamController::class, 'getDetail']);
    Route::put('/update/{id}', [SanPhamController::class, 'updateProduct']);
    Route::delete('/delete/{id}', [SanPhamController::class, 'deleteProduct']);
    Route::get('/getNewProducts', [SanPhamController::class, 'getNewProducts']);
    Route::get('/getBestSellingProduct', [SanPhamController::class, 'getBestSellingProduct']);
    Route::get('/danh-muc/{id}', [SanPhamController::class, 'getSanPhamTheoDanhMuc']);
    Route::get('/danh-muc-con/{id}', [SanPhamController::class, 'getSanPhamTheoDanhMucCon']);
    Route::get('/tim-kiem', [SanPhamController::class, 'timKiemSanPham']);
    Route::get('/loc-gia', [SanPhamController::class, 'locSanPhamTheoGia']);
    // Route::get('/thong-so-san-pham/{id}', [SanPhamController::class, 'getThongSoSanPham']);

    Route::get('/allSanPham', [SanPhamController::class, 'getAllSanPham']);
});
