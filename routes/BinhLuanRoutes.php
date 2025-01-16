<?php

use App\Http\Controllers\Api\BinhLuanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('binh-luan', BinhLuanController::class);

