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

});
