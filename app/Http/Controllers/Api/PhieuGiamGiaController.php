<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePhieuGiamGiaRequest;
use App\Models\PhieuGiamGia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhieuGiamGiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phieuGiamGias = PhieuGiamGia::all();

        return response()->json([
            'data' => $phieuGiamGias,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhieuGiamGiaRequest $request)
    {
        // Lấy dữ liệu đã được validate từ Request
        $validated = $request->validated();

        // Tự động sinh mã giảm giá
        $validated['ma_giam_gia'] = strtoupper(Str::random(10)); // Sinh chuỗi ngẫu nhiên gồm 10 ký tự

        // Tạo phiếu giảm giá
        $phieuGiamGia = PhieuGiamGia::create($validated);

        return response()->json([
            'message' => 'Phiếu giảm giá đã được tạo thành công.',
            'data' => $phieuGiamGia,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Tìm phiếu giảm giá theo ID
        $phieuGiamGia = PhieuGiamGia::find($id);

        // Kiểm tra nếu không tìm thấy
        if (!$phieuGiamGia) {
            return response()->json([
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        return response()->json([
            'data' => $phieuGiamGia,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Tìm phiếu giảm giá cần cập nhật
        $phieuGiamGia = PhieuGiamGia::find($id);

        if (!$phieuGiamGia) {
            return response()->json([
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        // Cập nhật thông tin phiếu giảm giá
        $phieuGiamGia->update($request->all());

        return response()->json([
            'message' => 'Phiếu giảm giá đã được cập nhật thành công.',
            'data' => $phieuGiamGia,
        ], 200);
    }

    public function destroy(string $id)
    {
        $phieuGiamGia = PhieuGiamGia::find($id);

        if (!$phieuGiamGia) {
            return response()->json([
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        // Xóa phiếu giảm giá
        $phieuGiamGia->delete();

        return response()->json([
            'message' => 'Phiếu giảm giá đã được xóa thành công.',
        ], 200);
    }
    public function layPhieuGiamGiaPhuHopVoiDonHang(Request $request)
    {
        // Lấy giá trị đơn hàng từ request
        $orderValue = $request['gia_tri_don_hang'];

        if (!$orderValue) {
            return response()->json([
                'message' => 'Vui lòng cung cấp giá trị đơn hàng.',
            ], 400);
        }

        // Lấy danh sách phiếu giảm giá phù hợp
        $data = PhieuGiamGia::where('gia_tri_don_hang_toi_thieu', '<=', $orderValue)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
