<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhSachYeuThichRequest;
use App\Models\DanhSachYeuThich;
use App\Models\SanPham; // Thêm model SanPham để kiểm tra xem sản phẩm có tồn tại không
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

    /**
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public function xoaSanPhamYeuThich(Request $request)
    {
        $khachHangId = $request->khach_hang_id;
        $sanPhamId = $request->san_pham_id;

        if (!$khachHangId || !$sanPhamId) {
            return response()->json(['message' => 'Vui lòng cung cấp đầy đủ thông tin.'], 400);
        }

        $yeuThich = DanhSachYeuThich::where('khach_hang_id', $khachHangId)
            ->where('san_pham_id', $sanPhamId)
            ->first();

        if (!$yeuThich) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong danh sách yêu thích.'], 404);
        }

        DanhSachYeuThich::destroy($yeuThich->id);

        return response()->json(['message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích.']);
    }

    /**
     * Thêm sản phẩm vào danh sách yêu thích
     */
    public function themSanPhamYeuThich(Request $request)
    {
        // Lấy dữ liệu từ request
        $khachHangId = $request->khach_hang_id;
        $sanPhamId = $request->san_pham_id;

        // Kiểm tra nếu thiếu dữ liệu cần thiết
        if (!$khachHangId || !$sanPhamId) {
            return response()->json(['message' => 'Vui lòng cung cấp đầy đủ thông tin.'], 400);
        }

        // Kiểm tra xem sản phẩm có tồn tại hay không
        $sanPham = SanPham::find($sanPhamId);

        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }

        // Kiểm tra nếu sản phẩm đã tồn tại trong danh sách yêu thích của khách hàng
        $existingYeuThich = DanhSachYeuThich::where('khach_hang_id', $khachHangId)
            ->where('san_pham_id', $sanPhamId)
            ->first();

        if ($existingYeuThich) {
            return response()->json(['message' => 'Sản phẩm đã có trong danh sách yêu thích.'], 400);
        }

        // Tạo mới danh sách yêu thích
        $yeuThich = new DanhSachYeuThich();
        $yeuThich->khach_hang_id = $khachHangId;
        $yeuThich->san_pham_id = $sanPhamId;
        $yeuThich->save();

        return response()->json(['message' => 'Sản phẩm đã được thêm vào danh sách yêu thích.']);
    }
}
