<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChiTietGioHang;
use App\Models\GioHang;
use App\Models\KhachHang;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GioHangController extends Controller
{
    // Xem giỏ hàng của khách hàng
    public function viewCart($khach_hang_id)
    {
        $gioHang = GioHang::where('khach_hang_id', $khach_hang_id)->with('chiTietGioHangs.sanPham')->first();
        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);
        }

        // Tính tổng tiền
        $tongTien = 0;
        foreach ($gioHang->chiTietGioHangs as $chiTiet) {
            $tongTien += $chiTiet->sanPham->gia * $chiTiet->so_luong;
        }

        return response()->json([
            'gio_hang' => $gioHang,
            'tong_tien' => $tongTien,
            // Có thể thêm các khoản phí khác ở đây (như thuế, phí vận chuyển)
        ], 200);
    }

    // Thêm sản phẩm vào giỏ hàng
    // Thêm sản phẩm vào giỏ hàng
    public function addProduct(Request $request): JsonResponse
    {
        $request->validate([
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'bien_the_san_pham_id' => 'nullable|exists:bien_the_san_phams,id',
            'gia' => 'required|numeric|min:0'
        ]);

        // Kiểm tra sự tồn tại của khách hàng
        $khachHang = KhachHang::find($request->khach_hang_id);
        if (!$khachHang) {
            return response()->json(['message' => 'Khách hàng không tồn tại'], 404);
        }

        // Kiểm tra sự tồn tại của sản phẩm
        $sanPham = SanPham::find($request->san_pham_id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Tìm hoặc tạo giỏ hàng cho khách hàng
        $gioHang = GioHang::firstOrCreate([
            'khach_hang_id' => $request->khach_hang_id,
            'trang_thai' => 'chua_thanh_toan'
        ]);

        // Kiểm tra nếu sản phẩm đã tồn tại trong giỏ hàng
        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $gioHang->id)
            ->where('san_pham_id', $request->san_pham_id)
            ->first();

        if ($chiTietGioHang) {
            // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
            $chiTietGioHang->so_luong += $request->so_luong; // Tăng số lượng sản phẩm hiện tại
            $chiTietGioHang->save();
        } else {
            // Nếu sản phẩm chưa có, thêm mới vào giỏ hàng
            $chiTietGioHang = ChiTietGioHang::create([
                'gio_hang_id' => $gioHang->id,
                'san_pham_id' => $request->san_pham_id,
                'bien_the_san_pham_id' => $request->bien_the_san_pham_id, // Truyền biến thể sản phẩm (nếu có)
                'so_luong' => $request->so_luong,
                'gia' => $request->gia
            ]);
        }

        return response()->json([
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công',
            'gio_hang' => $gioHang->load('chiTietGioHangs.sanPham')
        ], 200);
    }


    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateProduct(Request $request): JsonResponse
    {
        $request->validate([
            'gio_hang_id' => 'required|exists:gio_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1'
        ]);

        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $request->gio_hang_id)
            ->where('san_pham_id', $request->san_pham_id)
            ->first();

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

        $chiTietGioHang->so_luong = $request->so_luong;
        $chiTietGioHang->save();

        return response()->json(['message' => 'Cập nhật số lượng sản phẩm thành công', 'chi_tiet_gio_hang' => $chiTietGioHang], 200);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeProduct(Request $request)
    {
        $request->validate([
            'gio_hang_id' => 'required|exists:gio_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
        ]);

        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $request->gio_hang_id)
            ->where('san_pham_id', $request->san_pham_id)
            ->first();

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

        $chiTietGioHang->delete();

        return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
    }
}
