<?php

use App\Http\Controllers\Api\SaleSanPhamController;
use Illuminate\Support\Facades\Route;

Route::post('/sale-san-pham/add-sale', [SaleSanPhamController::class, 'addSale']);
Route::get('/sale-san-pham/get-sale', [SaleSanPhamController::class, 'getSaleSanPhams']);
