<?php

use App\Http\Controllers\Api\DanhMucConController;
use Illuminate\Support\Facades\Route;

Route::prefix('danh-muc-con')->group(function() {
    // Hiển thị danh sách các danh mục con
    Route::get('/find-by-id/{danh_muc_id}', [DanhMucConController::class, 'getSubCategoriesByCategoryId']);

    // Tạo mới danh mục con
    // Route::post('/create/', [DanhMucConController::class, 'store']);

    // // Hiển thị chi tiết một danh mục con
    Route::get('/detail/{id}', [DanhMucConController::class, 'show']);

    // // Cập nhật danh mục con
    // Route::put('/update/{id}', [DanhMucConController::class, 'update']);

    // // Xóa danh mục con
    // Route::delete('/delete/{id}', [DanhMucConController::class, 'destroy']);

    Route::get('/getAll', [DanhMucConController::class, 'getAll']);
});
Route::get('/danh-muc-con/{danh_muc_id}', [DanhMucConController::class, 'getSubCategoriesByCategoryId']);
Route::apiResource('danh-muc-con', DanhMucConController::class);

