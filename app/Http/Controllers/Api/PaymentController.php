<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BienTheSanPham;
use App\Models\ChiTietDonHang;
use App\Models\ChiTietGioHang;
use App\Models\DonHang;
use App\Models\PhieuGiamGia;
use App\Models\PhieuGiamGiaVaKhachHang;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createPayment(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'ma_don_hang' => 'required|unique:don_hangs,ma_don_hang',
            ]);

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:5173/thanh-toan";  // Điều chỉnh URL trả về
        $vnp_TmnCode = "3ZBLYQ7U";  // Mã website tại VNPAY
        $vnp_HashSecret = "9KEKQO6MDK6SYQROCD5GYSN9NBSOJH27"; // Chuỗi bí mật từ VNPAY

        $vnp_TxnRef = $validatedData['ma_don_hang'];
        $vnp_OrderInfo = "Thanh toán hóa đơn";
        $vnp_OrderType = "Hypertech Store";
        $vnp_Amount = $validatedData['amount'] * 100;
        $vnp_Locale = "VN";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (!empty($vnp_Bill_State)) {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url .= "?" . $query;
        if (!empty($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        $returnData = [
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url,
        ];

        if (!empty($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            return response()->json($returnData);
        }
    }

    public function handleVnPayCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        $responseCode = $request->query('vnp_ResponseCode');
        $transactionStatus = $request->query('vnp_TransactionStatus');
        $orderCode = $request->query('ma_don_hang');
        $customerId = $request->query('customer_id');
        $amount = $request->query('vnp_Amount') / 100;

        if ($responseCode === '00' && $transactionStatus === '00') {
            // Lấy thông tin đơn hàng từ frontend
            $orderData = [
                'ma_don_hang' => $orderCode,
                'khach_hang_id' => $customerId,
                'phuong_thuc_thanh_toan_id' => 2, // VNPay
                'tong_tien' => $amount,
                'trang_thai_don_hang' => 1, // Đã thanh toán
            ];

            // Tạo đơn hàng
            $order = $this->createOrderWithPaymentSuccess($orderData);

            return response()->json([
                'message' => 'Thanh toán thành công',
                'don_hang' => $order,
            ], 200);
        }

        return response()->json([
            'message' => 'Thanh toán thất bại',
        ], 400);
    }


    // Hàm tạo đơn hàng (cập nhật từ logic hiện tại)
    private function createOrderWithPaymentSuccess(array $orderData): \Illuminate\Http\JsonResponse
    {
        $donHang = DonHang::create([
            'ma_don_hang' => $orderData['ma_don_hang'],
            'khach_hang_id' => $orderData['khach_hang_id'],
            'phuong_thuc_thanh_toan_id' => $orderData['phuong_thuc_thanh_toan_id'],
            'hinh_thuc_van_chuyen_id' => $orderData['hinh_thuc_van_chuyen_id'],
            'trang_thai_don_hang' => 1, // Đang xử lý
            'tong_tien' => $orderData['tong_tien'],
            'dia_chi_giao_hang' => $orderData['dia_chi_giao_hang'],
        ]);

        $totalAmount = 0;

        foreach ($orderData['products'] as $product) {
            $productAttributes = json_encode($product['attributes']);

            $variant = BienTheSanPham::find($product['bien_the_san_pham_id']);

            $productTotal = $product['so_luong'] * $product['gia'];
            $totalAmount += $productTotal;

            // Thêm chi tiết đơn hàng
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

        // Xử lý mã giảm giá (nếu có)
        if (isset($orderData['ma_giam_gia'])) {
            $phieuGiamGia = PhieuGiamGia::where('ma_giam_gia', $orderData['ma_giam_gia'])
                ->whereDate('ngay_bat_dau', '<=', now())
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($phieuGiamGia) {
                $phieuGiamGia->decrement('so_luot_su_dung');

                PhieuGiamGiaVaKhachHang::create([
                    'phieu_giam_gia_id' => $phieuGiamGia->id,
                    'khach_hang_id' => $orderData['khach_hang_id'],
                    'don_hang_id' => $donHang->id,
                ]);
            }
        }

        // Xóa sản phẩm khỏi giỏ hàng
        $chiTietGioHangIds = array_column($orderData['products'], 'chi_tiet_gio_hang_id');
        ChiTietGioHang::whereIn('id', $chiTietGioHangIds)->delete();

        return response()->json([
            'message' => 'Đơn hàng đã được tạo thành công',
            'don_hang' => $donHang,
        ], 200);
    }
}
