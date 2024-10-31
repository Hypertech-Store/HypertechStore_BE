<?php

use App\Http\Controllers\Api\BienTheSanPhamController;
use App\Http\Controllers\Api\DanhMucConController;
use App\Http\Controllers\Api\DanhMucController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SanPhamController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('danhmucs', DanhMucController::class);

Route::apiResource('danhmuccons', DanhMucConController::class);

Route::apiResource('bienthesanphams', BienTheSanPhamController::class);
Route::get('/san-pham/{san_pham_id}/bien-the', [BienTheSanPhamController::class, 'getBienTheBySanPhamId']);

require __DIR__ . '/productRoutes.php';
