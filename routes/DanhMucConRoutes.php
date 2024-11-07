<?php

use App\Http\Controllers\Api\DanhMucConController;
use Illuminate\Support\Facades\Route;

Route::apiResource('danh-muc-con', DanhMucConController::class);
