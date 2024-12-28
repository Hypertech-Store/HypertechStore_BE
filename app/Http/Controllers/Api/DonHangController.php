<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonHangRequest;
use App\Models\BienTheSanPham;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\PhuongThucThanhToan;
use App\Models\ThanhToan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DonHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Số lượng đơn hàng mỗi trang
            $perPage = 5;

            // Lấy danh sách đơn hàng với các quan hệ liên quan
            $data = DonHang::with(['chiTietDonHangs.sanPham', 'phuongThucThanhToan'])
                ->paginate($perPage);

            // Kiểm tra nếu không có dữ liệu
            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng nào.'
                ], 404);
            }

            // Trả về dữ liệu đã phân trang
            return response()->json($data);
        } catch (\Exception $e) {
            // Trả về lỗi nếu xảy ra ngoại lệ
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình truy vấn.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDonHangRequest $request)
    {

        $data = DonHang::query()->create($request->all());

        return response()->json([
            'message' => 'Đơn hàng được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DonHang::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết đơn hàng id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa đơn hàng: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = DonHang::query()->findOrFail($id);

            $updatedData = [];
            if ($request->has('trang_thai_don_hang')) {
                $updatedData['trang_thai_don_hang'] = $request->input('trang_thai_don_hang');
            }

            if (!empty($updatedData)) {
                $data->update($updatedData);
            }


            return response()->json([
                'message' => 'Cập nhật trạng thái đơn hàng id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DonHang::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkout(Request $request)
    {
        // Xác thực dữ liệu trong request
        $request->validate([
            'khach_hang_id' => 'required|integer|exists:khach_hangs,id',
            'dia_chi_giao_hang' => 'required|string|max:255',
            'phuong_thuc_thanh_toan_id' => 'required|integer|exists:phuong_thuc_thanh_toans,id',
        ]);

        // Lấy tên phương thức thanh toán từ bảng phuong_thuc_thanh_toan
        $phuongThucThanhToan = PhuongThucThanhToan::find($request->phuong_thuc_thanh_toan_id);

        // Nếu không tìm thấy phương thức thanh toán
        if (!$phuongThucThanhToan) {
            return response()->json(['error' => 'Phương thức thanh toán không tồn tại'], 400);
        }

        // Lấy thông tin khách hàng từ request
        $khachHangId = $request->khach_hang_id;

        // Lấy giỏ hàng của khách hàng
        $gioHang = GioHang::where('khach_hang_id', $khachHangId)->first();

        // Kiểm tra nếu giỏ hàng trống
        if (!$gioHang || $gioHang->chiTietGioHangs->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng trống'], 400);
        }

        $tongTien = $gioHang->chiTietGioHangs->sum(function ($chiTiet) {
            // Kiểm tra nếu có biến thể sản phẩm
            if ($chiTiet->bien_the_san_pham_id) {
                $bienThe = BienTheSanPham::find($chiTiet->bien_the_san_pham_id);
                return $bienThe->gia * $chiTiet->so_luong; // Tính theo giá của biến thể
            }
            return $chiTiet->gia * $chiTiet->so_luong; // Tính theo giá của sản phẩm chính
        });

        // Tạo đơn hàng
        $donHang = DonHang::create([
            'khach_hang_id' => $khachHangId,
            'trang_thai_don_hang' => 'Đang xử lý',
            'tong_tien' => $tongTien,
            'dia_chi_giao_hang' => $request->dia_chi_giao_hang,
            'phuong_thuc_thanh_toan' => $phuongThucThanhToan->ten_phuong_thuc,  // Lưu tên phương thức thanh toán
        ]);

        // Tạo chi tiết đơn hàng từ giỏ hàng
        foreach ($gioHang->chiTietGioHangs as $chiTiet) {
            ChiTietDonHang::create([
                'don_hang_id' => $donHang->id,
                'san_pham_id' => $chiTiet->san_pham_id,
                'so_luong' => $chiTiet->so_luong,
                'gia' => $chiTiet->gia,
            ]);
        }

        // Tạo thông tin thanh toán
        ThanhToan::create([
            'don_hang_id' => $donHang->id,
            'phuong_thuc_thanh_toan_id' => $phuongThucThanhToan->id,
            'so_tien_thanh_toan' => $tongTien,
        ]);

        // Xóa chi tiết giỏ hàng sau khi thanh toán thành công
        $gioHang->chiTietGioHangs()->delete();

        // Trả về thông tin đơn hàng đã tạo
        return response()->json(['message' => 'Đơn hàng đã được tạo thành công', 'don_hang' => $donHang], 201);
    }
}
