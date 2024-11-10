<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhSachYeuThichRequest;
use App\Models\DanhSachYeuThich;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DanhSachYeuThichController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function danhSachYeuThich($khachHangId)
    {
        $data = DanhSachYeuThich::with('sanPham')
            ->where('khach_hang_id', $khachHangId)
            ->get();

        return response()->json($data);
    }
    public function xoaSanPhamYeuThich(Request $request)
    {
        // Lấy dữ liệu từ request
        $khachHangId = $request->khach_hang_id;
        $sanPhamId = $request->san_pham_id;

        // Kiểm tra nếu thiếu dữ liệu cần thiết
        if (!$khachHangId || !$sanPhamId) {
            return response()->json([
                'message' => 'Vui lòng cung cấp đầy đủ thông tin.'
            ], 400);
        }

        // Tìm sản phẩm trong danh sách yêu thích của khách hàng
        $yeuThich = DanhSachYeuThich::where('khach_hang_id', $khachHangId)
            ->where('san_pham_id', $sanPhamId)
            ->first();

        // Kiểm tra nếu không tìm thấy sản phẩm
        if (!$yeuThich) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại trong danh sách yêu thích của khách hàng.'
            ], 404);
        }
        DanhSachYeuThich::destroy($yeuThich->id);


        // Xóa sản phẩm khỏi danh sách yêu thích

        return response()->json([
            'message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích.',

        ]);
    }

}
