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
        // Phương thức thanh toán là các phương thức khác, tạo đơn hàng trực tiếp
        return $this->createOrderWithPaymentSuccess($request);
    }

    private function handleVnPayPayment(Request $request)
    {

        return [
            'success' => true,
        ];
    }

    private function createOrderWithPaymentSuccess(Request $request)
    {
        $donHang = DonHang::create([
            'ma_don_hang' => $request->ma_don_hang,
            'khach_hang_id' => $request->khach_hang_id,
            'phuong_thuc_thanh_toan_id' => $request->phuong_thuc_thanh_toan_id,
            'hinh_thuc_van_chuyen_id' => $request->hinh_thuc_van_chuyen_id,
            'trang_thai_don_hang' => 1,
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


    public function viewOrder(Request $request, $khach_hang_id): \Illuminate\Http\JsonResponse
    {
        $page = $request->query('page', 1);  // Get 'page' from the query, default to 1
        $numberRow = $request->query('number_row', 5); // Get 'number_row' from the query, default to 5

        $donHangs = DonHang::with(['chiTietDonHangs.sanPham', 'phuongThucThanhToan', 'trangThaiDonHang'])
            ->where('khach_hang_id', $khach_hang_id)
            ->paginate($numberRow, ['*'], 'page', $page);

        // Check if there are no orders
        if ($donHangs->isEmpty()) {
            return response()->json(['message' => 'Không có đơn hàng nào của khách hàng này'], 404);
        }

        // Convert thuoc_tinh to JSON
        $donHangs->getCollection()->transform(function ($donHang) {
            $donHang->chiTietDonHangs->each(function ($chiTietDonHang) {
                $chiTietDonHang->thuoc_tinh = json_decode($chiTietDonHang->thuoc_tinh);
            });
            return $donHang;
        });

        return response()->json([
            'don_hangs' => $donHangs,
            'current_page' => $donHangs->currentPage(),
            'total_pages' => $donHangs->lastPage(),
        ], 200);
    }



    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus(Request $request, $don_hang_id)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'trang_thai_don_hang_id' => 'required|exists:trang_thai_don_hangs,id',
        ]);

        // Tìm đơn hàng theo ID
        $donHang = DonHang::find($don_hang_id);

        // Kiểm tra xem đơn hàng có tồn tại không
        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Cập nhật trạng thái đơn hàng
        $donHang->trang_thai_don_hang_id = $request->trang_thai_don_hang_id;
        $donHang->save(); // Lưu thay đổi

        // Trả về thông báo thành công và dữ liệu đơn hàng đã cập nhật
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
