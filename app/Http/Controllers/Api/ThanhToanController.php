<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreThanhToanRequest;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\ThanhToan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ThanhToanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ThanhToan::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThanhToanRequest $request)
    {

        $data = ThanhToan::query()->create($request->all());

        return response()->json([
            'message' => 'Thanh toán được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = ThanhToan::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết thanh toán id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy thanh toán id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa thanh toán: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy thanh toán id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreThanhToanRequest $request, string $id)
    {
        try {
            $data = ThanhToan::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật thanh toán id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thanh toán id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật thanh toán: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật thanh toán',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            ThanhToan::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thanh toán id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa thanh toán: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa thanh toán',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function layDonHangCuaKhangHang($khachHangId, $donHangId)
    {
        // Lấy ra đơn hàng theo khach_hang_id và don_hang_id
        $donHang = DonHang::where('khach_hang_id', $khachHangId)
                        ->where('id', $donHangId)
                        ->first();

        if (!$donHang) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng này của khách hàng'], 404);
        }

        // Lấy thông tin đơn hàng và thanh toán kèm theo
        $donHangInfo = [
            'don_hang_id' => $donHang->id,
            'tong_tien' => $donHang->tong_tien,
            'dia_chi_giao_hang' => $donHang->dia_chi_giao_hang,
            'phuong_thuc_thanh_toan' => $donHang->phuong_thuc_thanh_toan,
            'trang_thai_don_hang' => $donHang->trang_thai_don_hang,
            'created_at' => $donHang->created_at,
        ];

        return response()->json([
            'khach_hang_id' => $khachHangId,
            'don_hang' => $donHangInfo,
        ]);
    }
    public function createVnpayPaymentUrl($don_hang_id)
    {
        $donHang = DonHang::find($don_hang_id); // Truy vấn đơn hàng

        if (!$donHang) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $vnp_TmnCode = 'VNPAY_TMNCODE';  // Mã cửa hàng của bạn tại VNPAY
        $vnp_HashSecret = 'VNPAY_HASHSECRET'; // Mã bí mật của bạn tại VNPAY

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán của VNPAY
        $vnp_TxnRef = $donHang->id;  // Mã đơn hàng
        $vnp_OrderInfo = "Thanh toán cho đơn hàng #" . $donHang->id;
        $vnp_Amount = $donHang->tong_tien * 100;  // Số tiền (đơn vị là đồng)
        $vnp_Locale = 'vn';  // Ngôn ngữ
        $vnp_BankCode = ''; // Mã ngân hàng (nếu có)

        $vnp_ReturnUrl = url('/vnpay-return'); // URL trả về sau khi thanh toán

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Locale" => $vnp_Locale,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_IpAddr" => request()->ip(),
            "vnp_CreateDate" => now()->format('YmdHis'),  // Định dạng thời gian chính xác
        ];

        ksort($inputData);  // Sắp xếp mảng theo thứ tự bảng chữ cái

        $query = http_build_query($inputData);  // Chuyển mảng thành chuỗi query string
        $hashData = $query . "&vnp_HashSecret=" . $vnp_HashSecret;  // Thêm mã bí mật vào
        $vnp_SecureHash = strtoupper(md5($hashData));  // Tạo chữ ký an toàn

        $url = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

        return $url;
    }


}
