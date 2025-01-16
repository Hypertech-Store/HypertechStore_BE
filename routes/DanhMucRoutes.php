<?php

use App\Http\Controllers\Api\DanhMucController;
use Illuminate\Support\Facades\Route;

Route::prefix('danh-muc')->group(function () {
    // Hiển thị danh sách các danh mục
    Route::get('/getAll', [DanhMucController::class, 'getAllDanhMuc']);
    Route::put('/trang-thai', [DanhMucController::class, 'updateStatus']);
    // Tạo mới danh mục
    // Route::post('/addNew', [DanhMucController::class, 'store']);

    // // Hiển thị chi tiết một danh mục
    // Route::get('/detail/{id}', [DanhMucController::class, 'show']);

    // // Cập nhật danh mục
    // Route::put('/update/{id}', [DanhMucController::class, 'update']);

    // // Xóa danh mục
    // Route::delete('/delete/{id}', [DanhMucController::class, 'destroy']);
});
Route::apiResource('danh-muc', DanhMucController::class);
