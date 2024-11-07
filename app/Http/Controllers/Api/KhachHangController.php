<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKhachHangRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KhachHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function login(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|min:8',
        ]);

        // Xác thực thông tin đăng nhập
        $credentials = [
            'email' => $request->email,
            'password' => $request->mat_khau,
        ];

        if (Auth::guard('khachhang')->attempt($credentials)) {
            // Đăng nhập thành công
            return response()->json([
                'message' => 'Đăng nhập thành công!',
                // 'redirect_url' => '/khach-hang/dashboard'
            ], 200);
        }

        // Đăng nhập thất bại
        return response()->json([
            'error' => 'Thông tin đăng nhập không chính xác.',
        ], 401);
    }
    /**
     * Xử lý đăng ký khách hàng mới.
     */
    public function register(StoreKhachHangRequest $request)
    {
        $khachHang = KhachHang::query()->create($request->all());

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
    public function logout()
    {
        Auth::guard('khachhang')->logout();

        // Trả về phản hồi JSON
        return response()->json([
            'message' => 'Đăng xuất thành công!',
        ], 200);
    }
}
