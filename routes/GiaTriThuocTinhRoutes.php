<?php

use App\Http\Controllers\Api\GiaTriThuocTinhController;
use Illuminate\Support\Facades\Route;

Route::apiResource('gia-tri-thuoc-tinh', GiaTriThuocTinhController::class);
