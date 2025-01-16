<?php

use App\Http\Controllers\Api\ThongSoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('thong-so', ThongSoController::class);
