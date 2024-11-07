<?php

use App\Http\Controllers\Api\ThanhToanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('thanh-toan', ThanhToanController::class);
