<?php

use App\Http\Controllers\Api\SaleSanPhamController;
use Illuminate\Support\Facades\Route;

Route::post('/sale-san-pham', [SaleSanPhamController::class, 'addSale']);
