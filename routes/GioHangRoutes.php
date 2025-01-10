<?php

use App\Http\Controllers\Api\GioHangController;
use Illuminate\Support\Facades\Route;

Route::prefix('gio-hang')->group(function () {
     Route::post('/them-gio-hang', [GioHangController::class, 'addProduct']);         // Thêm sản phẩm vào giỏ hàng
     Route::put('/cap-nhat-gio-hang', [GioHangController::class, 'updateProduct']);   // Cập nhật số lượng sản phẩm
     Route::post('/xoa-gio-hang', [GioHangController::class, 'removeProduct']);        // Xóa sản phẩm khỏi giỏ hàng
     Route::get('/{khach_hang_id}', [GioHangController::class, 'viewCart']);           // Xem giỏ hàng của khách hàng
     Route::delete('/xoa-gio-hang/{khach_hang_id}', [GioHangController::class, 'xoaGioHang']);
});
