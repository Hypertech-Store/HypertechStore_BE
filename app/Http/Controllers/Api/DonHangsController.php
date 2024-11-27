<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\GioHang;
use App\Models\PhuongThucThanhToan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DonHangsController extends Controller
{
    // Tạo đơn hàng
    public function createOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'dia_chi_giao_hang' => 'required|string',
            'phuong_thuc_thanh_toan' => 'required|exists:phuong_thuc_thanh_toans,id',
        ]);

        // Tạo đơn hàng
        $donHang = DonHang::create([
            'khach_hang_id' => $request->khach_hang_id,
            'trang_thai_don_hang' => 'pending', // or any default status
            'tong_tien' => 0, // Will calculate later
            'dia_chi_giao_hang' => $request->dia_chi_giao_hang,
            'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
            'created_at' => Carbon::now(),
        ]);

        // Lấy giỏ hàng của khách hàng
        $gioHang = GioHang::where('khach_hang_id', $request->khach_hang_id)->first();
        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);
        }

        $totalAmount = 0;
        foreach ($gioHang->chiTietGioHangs as $chiTietGioHang) {
            $chiTietDonHang = ChiTietDonHang::create([
                'don_hang_id' => $donHang->id,
                'san_pham_id' => $chiTietGioHang->san_pham_id,
                'so_luong' => $chiTietGioHang->so_luong,
                'gia' => $chiTietGioHang->gia,
            ]);

            $totalAmount += $chiTietGioHang->gia * $chiTietGioHang->so_luong;
        }

        // Cập nhật tổng tiền của đơn hàng
        $donHang->update(['tong_tien' => $totalAmount]);

        // Xóa giỏ hàng của khách hàng sau khi tạo đơn
        $gioHang->chiTietGioHangs()->delete();
        $gioHang->delete();

        return response()->json([
            'message' => 'Đơn hàng đã được tạo thành công',
            'don_hang' => $donHang,
        ], 200);
    }

    // Xem đơn hàng của khách hàng
    public function viewOrder($don_hang_id): \Illuminate\Http\JsonResponse
    {
        $donHang = DonHang::with('khachHang', 'chiTietDonHangs.sanPham')->find($don_hang_id);

        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        return response()->json([
            'don_hang' => $donHang,
        ], 200);
    }

    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus(Request $request, $don_hang_id)
    {
        $request->validate([
            'trang_thai_don_hang' => 'required|string',
        ]);

        $donHang = DonHang::find($don_hang_id);
        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        $donHang->update(['trang_thai_don_hang' => $request->trang_thai_don_hang]);

        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'don_hang' => $donHang,
        ], 200);
    }

    // Thanh toán đơn hàng
    public function processPayment(Request $request, $don_hang_id): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phuong_thuc_thanh_toan' => 'required|exists:phuong_thuc_thanh_toans,id',
        ]);

        $donHang = DonHang::find($don_hang_id);
        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Xử lý thanh toán ở đây, ví dụ sử dụng phương thức thanh toán qua ngân hàng hoặc cổng thanh toán.
        // Đảm bảo thay đổi trạng thái đơn hàng sau khi thanh toán.
        $donHang->update([
            'trang_thai_don_hang' => 'paid', // Hoặc trạng thái đã thanh toán khác
        ]);

        return response()->json([
            'message' => 'Thanh toán thành công',
            'don_hang' => $donHang,
        ], 200);
    }
}
