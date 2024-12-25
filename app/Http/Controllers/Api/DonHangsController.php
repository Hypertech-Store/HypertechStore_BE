<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BienTheSanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\ChiTietGioHang;
use App\Models\GioHang;
use App\Models\PhieuGiamGia;
use App\Models\PhieuGiamGiaVaKhachHang;
use App\Models\PhuongThucThanhToan;
use App\Models\SanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonHangsController extends Controller
{
    // Tạo đơn hàng
    public function createOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate incoming request
        $request->validate([
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toans,id',
            'dia_chi_giao_hang' => 'required|string|max:255',
            'products' => 'required|array',
            'products.*.san_pham_id' => 'required|exists:san_phams,id',
            'products.*.bien_the_san_pham_id' => 'required|exists:bien_the_san_phams,id',
            'products.*.attributes' => 'required|array',
            'products.*.so_luong' => 'required|integer|min:1',
            'products.*.gia' => 'required|numeric|min:0',
        ]);

        // Phương thức thanh toán là VNPay (ID 2), xử lý thanh toán
        if ($request->phuong_thuc_thanh_toan_id == 2) {
            // Logic thanh toán VNPay (cần tích hợp API của VNPay)
            $vnpayResponse = $this->handleVnPayPayment($request);
            $vnpayResponse['success'] = false;
            // Nếu thanh toán không thành công, trả về lỗi
            if (!$vnpayResponse['success']) {
                return response()->json([
                    'message' => 'Thanh toán VNPay thất bại, vui lòng thử lại!',
                ], 400);
            }

            // Nếu thanh toán thành công, tạo đơn hàng
            return $this->createOrderWithPaymentSuccess($request);
        }

        // Phương thức thanh toán là các phương thức khác, tạo đơn hàng trực tiếp
        return $this->createOrderWithPaymentSuccess($request);
    }

    private function handleVnPayPayment(Request $request)
    {

        return [
            'success' => true,
        ];
    }
    private function generateUniqueOrderCode($length = 10) {
        do {
            // Tạo số ngẫu nhiên hoặc tuần tự
            $soNgauNhien = mt_rand(100, 9999999); // Tạo số từ 100000 đến 999999
            // Ghép "DH" với số ngẫu nhiên
            $maDonHang = 'DH' . $soNgauNhien;
        } while (DonHang::where('ma_don_hang', $maDonHang)->exists()); // Kiểm tra mã đã tồn tại

        return $maDonHang;
    }
    private function createOrderWithPaymentSuccess(Request $request)
    {

        $donHang = DonHang::create([
            'ma_don_hang' =>  $this->generateUniqueOrderCode(),
            'khach_hang_id' => $request->khach_hang_id,
            'phuong_thuc_thanh_toan_id' => $request->phuong_thuc_thanh_toan_id,
            'hinh_thuc_van_chuyen_id' => $request->hinh_thuc_van_chuyen_id,
            'trang_thai_don_hang' => 'Chờ xử lý',
            'tong_tien' => $request->tong_tien,
            'dia_chi_giao_hang' => $request->dia_chi_giao_hang,
        ]);

        $totalAmount = 0;

        foreach ($request->products as $product) {
            $productAttributes = json_encode($product['attributes']);

            $variant = BienTheSanPham::find($product['bien_the_san_pham_id']);

            $productTotal = $product['so_luong'] * $product['gia'];
            $totalAmount += $productTotal;

            // Create the order details (ChiTietDonHang)
            ChiTietDonHang::create([
                'don_hang_id' => $donHang->id,
                'san_pham_id' => $product['san_pham_id'],
                'bien_the_san_pham_id' => $product['bien_the_san_pham_id'],
                'thuoc_tinh' => $productAttributes,
                'so_luong' => $product['so_luong'],
                'gia' => $product['gia'],
            ]);

            if ($variant) {
                $variant->decrement('so_luong_kho', $product['so_luong']);
            }
        }

        $discountAmount = 0;

        // Kiểm tra mã giảm giá (nếu có)
        if ($request->has('ma_giam_gia')) {
            $phieuGiamGia = PhieuGiamGia::where('ma_giam_gia', $request->ma_giam_gia)
                ->whereDate('ngay_bat_dau', '<=', now())
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($phieuGiamGia) {
                // Giảm số lượt sử dụng của mã giảm giá
                $phieuGiamGia->decrement('so_luot_su_dung');

                // Lưu thông tin mã giảm giá vào bảng liên kết
                PhieuGiamGiaVaKhachHang::create([
                    'phieu_giam_gia_id' => $phieuGiamGia->id,
                    'khach_hang_id' => $request->khach_hang_id,
                    'don_hang_id' => $donHang->id,
                ]);
            }
        }
        $chi_tiet_gio_hang_id = array_column($request->products, 'chi_tiet_gio_hang_id');
        ChiTietGioHang::whereIn('id', $chi_tiet_gio_hang_id)->delete();

        // Return success response
        return response()->json([
            'message' => 'Đơn hàng đã được tạo thành công',
            'don_hang' => $donHang,
        ], 200);
    }


    public function viewOrder($khach_hang_id): \Illuminate\Http\JsonResponse
    {
        // Lấy tất cả đơn hàng của khách hàng cùng với chi tiết đơn hàng và sản phẩm
        $donHangs = DonHang::with(['chiTietDonHangs.sanPham'])
            ->where('khach_hang_id', $khach_hang_id) // Lọc đơn hàng theo khách hàng
            ->get();

        // Kiểm tra nếu không có đơn hàng nào của khách hàng
        if ($donHangs->isEmpty()) {
            return response()->json(['message' => 'Không có đơn hàng nào của khách hàng này'], 404);
        }

        // Trả về dữ liệu các đơn hàng của khách hàng
        return response()->json([
            'don_hangs' => $donHangs,
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
