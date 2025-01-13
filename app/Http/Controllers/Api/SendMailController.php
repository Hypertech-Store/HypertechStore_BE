<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentSuccessMail;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendMailController extends Controller
{
    public function sendPaymentSuccessMail(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'khach_hang_id' => 'required|integer',
            'order_id' => 'required|integer', // Lấy order_id thay vì ma_don_hang
            'payment_time' => 'required|date',
        ]);

        // Truy vấn thông tin khách hàng
        $khachHang = KhachHang::find($validated['khach_hang_id']);
        if (!$khachHang) {
            return response()->json(['message' => 'Khách hàng không tồn tại'], 404);
        }

        // Truy vấn thông tin đơn hàng bằng order_id
        $orderData = DB::table('don_hangs AS d')
            ->select([
                'd.id AS order_id', // ID đơn hàng
                'd.ma_don_hang',    // Mã đơn hàng
                'd.tong_tien',      // Tổng tiền
                'd.dia_chi_giao_hang', // Địa chỉ giao hàng
                'htvc.mo_ta AS ngay_giao_du_kien', // Ngày giao dự kiến
                'htvc.gia_van_chuyen',  // Giá vận chuyển
                'p.ten_san_pham',   // Tên sản phẩm
                'ct.so_luong',      // Số lượng sản phẩm
                'ct.gia',           // Giá sản phẩm
                'phieuGiamGia.ma_giam_gia', // Mã giảm giá
                'phieuGiamGia.gia_tri_giam_gia' // Giá trị giảm giá
            ])
            ->join('chi_tiet_don_hangs AS ct', 'd.id', '=', 'ct.don_hang_id')
            ->join('san_phams AS p', 'ct.san_pham_id', '=', 'p.id')
            ->join('hinh_thuc_van_chuyens AS htvc', 'd.hinh_thuc_van_chuyen_id', '=', 'htvc.id')
            ->leftJoin('phieu_giam_gia_va_khach_hangs AS pgk', 'pgk.don_hang_id', '=', 'd.id')
            ->leftJoin('phieu_giam_gias AS phieuGiamGia', 'pgk.phieu_giam_gia_id', '=', 'phieuGiamGia.id')
            ->where('d.id', $validated['order_id']) // Sử dụng order_id
            ->get();

        if ($orderData->isEmpty()) {
            return response()->json(['message' => 'Đơn hàng không tồn tại'], 404);
        }

        // Xử lý chi tiết sản phẩm trong đơn hàng
        $orderDetails = $orderData->map(function ($detail) {
            return [
                'ten_san_pham' => $detail->ten_san_pham,
                'so_luong' => $detail->so_luong,
                'gia' => $detail->gia,
            ];
        });

        // Tính toán dữ liệu tổng quát
        $firstOrder = $orderData->first(); // Lấy bản ghi đầu tiên làm dữ liệu tổng quát

        // Chuẩn bị dữ liệu cho email
        // Chuẩn bị dữ liệu cho email
        $mailData = [
            'order_id' => $firstOrder->order_id,
            'ma_don_hang' => $firstOrder->ma_don_hang,
            'total' => $firstOrder->tong_tien,
            'payment_time' => $validated['payment_time'],
            'khach_hang_name' => $khachHang->ho_ten, // Kiểm tra việc lấy họ tên khách hàng ở đây
            'email' => $khachHang->email,
            'phi_van_chuyen' => $firstOrder->gia_van_chuyen,
            'ngay_giao_du_kien' => $firstOrder->ngay_giao_du_kien,
            'giam_gia' => $firstOrder->gia_tri_giam_gia,
            'dia_chi_giao_hang' => $firstOrder->dia_chi_giao_hang,
            'chi_tiet_san_pham' => $orderDetails,
        ];


        // Gửi email
        Mail::to($khachHang->email)->send(new PaymentSuccessMail($mailData));

        return response()->json(['message' => 'Email đã được gửi thành công!'], 200);
    }
}
