<?php

use App\Http\Controllers\Api\DinhKemController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dinh-kem', DinhKemController::class);

