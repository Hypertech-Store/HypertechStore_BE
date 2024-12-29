<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createPayment(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'required|string|unique:orders,order_id',
        ]);

        $paymentUrl = $this->generateVPPayUrl($validatedData['amount'], $validatedData['order_id']);

        return response()->json(['payment_url' => $paymentUrl]);
    }

    private function generateVPPayUrl($amount, $orderId): string
    {
        $vnp_TmnCode = env('VPPAY_TMNCODE');
        $vnp_HashSecret = env('VPPAY_HASHSECRET');
        $vnp_Url = env('VPPAY_ENDPOINT');
        $vnp_ReturnUrl = env('VPPAY_CALLBACK_URL');
        $vnp_TxnRef = $orderId; // Mã giao dịch
        $vnp_OrderInfo = "Payment for order $orderId";
        $vnp_Amount = $amount * 100; // Đơn vị là VND * 100
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // Sắp xếp dữ liệu theo thứ tự key
        ksort($inputData);
        $query = http_build_query($inputData);

        $hashData = urldecode(http_build_query($inputData));
        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnpSecureHash;
    }

    public function handleCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        $vnp_HashSecret = env('VPPAY_HASHSECRET');
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        // Loại bỏ `vnp_SecureHash` để tạo lại checksum
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));
        $generatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($generatedHash !== $vnp_SecureHash) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($inputData['vnp_ResponseCode'] == '00') {
            // Thanh toán thành công
            // Cập nhật trạng thái đơn hàng tại đây
            return response()->json(['message' => 'Payment successful', 'data' => $inputData]);
        } else {
            return response()->json(['message' => 'Payment failed', 'data' => $inputData], 400);
        }
    }
}
