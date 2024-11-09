<?php

use App\Http\Controllers\Api\DanhMucController;
use Illuminate\Support\Facades\Route;

Route::apiResource('danh-muc', DanhMucController::class);
