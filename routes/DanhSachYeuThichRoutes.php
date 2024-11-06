<?php

use App\Http\Controllers\Api\DanhSachYeuThichController;
use Illuminate\Support\Facades\Route;

Route::apiResource('danh-sach-yeu-thich', DanhSachYeuThichController::class);
