<?php

use App\Http\Controllers\Api\SaleSanPhamController;
use Illuminate\Support\Facades\Route;


Route::get('/sale-san-pham/get-sale', [SaleSanPhamController::class, 'getSaleSanPhams']);
Route::post('/sale-san-pham/add-sale', [SaleSanPhamController::class, 'addSale']);
Route::get('/sale-san-pham/chi-tiet-sale/{sale_san_pham_id}/', [SaleSanPhamController::class, 'detailsProductSale']);
Route::put('/sale-san-pham/{sale_san_pham_id}', [SaleSanPhamController::class, 'editSaleSanPham']);
Route::delete('/sale-san-pham/{sale_san_pham_id}', [SaleSanPhamController::class, 'deleteSaleSanPham']);
Route::get('/sale-san-pham/get-sale-paginate', [SaleSanPhamController::class, 'getSaleSanPhamPaginate']);
