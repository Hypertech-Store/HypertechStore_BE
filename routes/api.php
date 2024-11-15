<?php

use App\Http\Controllers\Api\BienTheSanPhamController;
use App\Http\Controllers\Api\DanhMucConController;
use App\Http\Controllers\Api\DanhMucController;
use App\Http\Controllers\Api\HinhAnhSanPhamController;
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

require __DIR__ . '/DanhMucRoutes.php';

require __DIR__ . '/DanhMucConRoutes.php';

require __DIR__ . '/HinhAnhSanPhamRoutes.php';

require __DIR__ . '/BienTheSanPhamRoutes.php';

require __DIR__ . '/DanhSachYeuThichRoutes.php';

require __DIR__ . '/DonHangRoutes.php';

require __DIR__ . '/ChiTietDonHangRoutes.php';

require __DIR__ . '/KhachHangRoutes.php';

require __DIR__ . '/PhuongThucThanhToanRoutes.php';

require __DIR__ . '/ThanhToanRoutes.php';

require __DIR__ . '/DanhGiaRoutes.php';

require __DIR__ . '/DinhKemRoutes.php';

require __DIR__ . '/BinhLuanRoutes.php';

require __DIR__ . '/SaleSanPhamRoutes.php';

require __DIR__ . '/productRoutes.php';

require __DIR__ . '/GioHangRoutes.php';

require __DIR__ . '/ForgotPasswordRouters.php';

