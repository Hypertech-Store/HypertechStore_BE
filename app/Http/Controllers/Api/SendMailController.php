<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentSuccessMail;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function sendPaymentSuccessMail(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate orderData
        $validated = $request->validate([
            'khach_hang_id' => 'required|integer',
            'order_id' => 'required|string',
            'total' => 'required|numeric',
            'payment_time' => 'required|date',
        ]);

        // Truy vấn thông tin khách hàng
        $khachHang = KhachHang::find($validated['khach_hang_id']);
        if (!$khachHang) {
            return response()->json(['message' => 'Khách hàng không tồn tại'], 404);
        }

        // Chuẩn bị dữ liệu để gửi mail
        $orderData = [
            'order_id' => $validated['order_id'],
            'total' => $validated['total'],
            'payment_time' => $validated['payment_time'],
            'khach_hang_name' => $khachHang->ho_ten,
            'email' => $khachHang->email,
        ];

        // Gửi email
        Mail::to($khachHang->email)->send(new PaymentSuccessMail($orderData));

        return response()->json(['message' => 'Email đã được gửi thành công!'], 200);
    }
}
