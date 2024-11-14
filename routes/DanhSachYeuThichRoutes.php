<?php

use App\Http\Controllers\Api\DanhSachYeuThichController;
use Illuminate\Support\Facades\Route;

// Route::apiResource('danh-sach-yeu-thich', DanhSachYeuThichController::class);
Route::get('/danh-sach-yeu-thich/{khachHangId}', [DanhSachYeuThichController::class, 'danhSachYeuThich']);
Route::post('/danh-sach-yeu-thich/destroy', [DanhSachYeuThichController::class, 'xoaSanPhamYeuThich']);
<<<<<<< HEAD
// Route thêm sản phẩm vào danh sách yêu thích
Route::post('/danh-sach-yeu-thich/addWishlist', [DanhSachYeuThichController::class, 'themSanPhamYeuThich']);
=======
Route::post('/danh-sach-yeu-thich/add', [DanhSachYeuThichController::class, 'themSanPhamVaoDanhSachYeuThich']);
>>>>>>> 0b6cdac180a98b25933e6d1e7e0301f08c4cdf3b
