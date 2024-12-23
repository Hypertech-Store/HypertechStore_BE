<?php

use App\Http\Controllers\Api\PhieuGiamGiaController;
use Illuminate\Support\Facades\Route;

Route::resource('phieu-giam-gia', PhieuGiamGiaController::class);
