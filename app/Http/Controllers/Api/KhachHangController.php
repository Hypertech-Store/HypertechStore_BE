<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKhachHangRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class KhachHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function login(Request $request): \Illuminate\Http\JsonResponse
     {
         // Xác thực dữ liệu đầu vào
         $request->validate([
             'email' => 'required|email',
             'mat_khau' => 'required|min:8',
         ]);

         // Tìm người dùng theo email
         $khachHang = KhachHang::where('email', $request->email)->first();

         // Kiểm tra thông tin đăng nhập
         if ($khachHang && Hash::check($request->mat_khau, $khachHang->mat_khau)) {

            Auth::login($khachHang);

             return response()->json([
                 'message' => 'Đăng nhập thành công!',
                 'data' => $khachHang
             ], Response::HTTP_OK);
         } else {
             return response()->json([
                 'error' => 'Thông tin đăng nhập không chính xác.'
             ], Response::HTTP_UNAUTHORIZED);
         }
     }
    /**
     * Xử lý đăng ký khách hàng mới.
     */
    public function register(StoreKhachHangRequest $request)
    {
        $khachHang = KhachHang::query()->create( [
            'ten_nguoi_dung' => $request->ten_nguoi_dung,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
        ]);

        // Trả về phản hồi JSON
        return response()->json([
            'message' => 'Đăng ký thành công !',
            'data' => $khachHang
            // 'redirect_url' => '/khach-hang/dashboard'
        ], Response::HTTP_CREATED);
    }

    /**
     * Xử lý đăng xuất khách hàng.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Đăng xuất thành công'
        ], 200);
    }
}
