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
            'ma_don_hang' => 'required|unique:don_hangs,ma_don_hang',
            ]);

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:5173/thanh-toan";
        $vnp_TmnCode = "RG33NYQY"; // Mã website tại VNPAY
        $vnp_HashSecret = "O876ZTZE7JGSUWKNUM4F6RKV25YJMCJT"; // Chuỗi bí mật

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

    public function handleCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        $vnp_HashSecret = env('VPPAY_HASHSECRET');
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        // Loại bỏ `vnp_SecureHash` để tạo lại checksum
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));
        $generatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($generatedHash !== $vnp_SecureHash) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($inputData['vnp_ResponseCode'] === '00') {
            // Thanh toán thành công
            // Cập nhật trạng thái đơn hàng tại đây
            return response()->json(['message' => 'Payment successful', 'data' => $inputData]);
        } else {
            return response()->json(['message' => 'Payment failed', 'data' => $inputData], 400);
        }
    }
}
