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


}
