<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrangThaiDonHang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrangThaiDonHangController extends Controller
{
    public function index()
    {
        $trangThaiDonHangs = TrangThaiDonHang::all();
        return response()->json($trangThaiDonHangs);
    }

    /**
     * Tạo mới trạng thái đơn hàng.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ten_trang_thai' => 'required|string|max:255',
            'mo_ta' => 'required|string',
        ]);

        $trangThaiDonHang = TrangThaiDonHang::create([
            'ten_trang_thai' => $request->ten_trang_thai,
            'mo_ta' => $request->mo_ta,
        ]);

        return response()->json($trangThaiDonHang, Response::HTTP_CREATED);
    }

    /**
     * Hiển thị thông tin một trạng thái đơn hàng.
     */
    public function show(string $id)
    {
        $trangThaiDonHang = TrangThaiDonHang::find($id);

        if (!$trangThaiDonHang) {
            return response()->json(['message' => 'Trạng thái đơn hàng không tồn tại.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($trangThaiDonHang);
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'ten_trang_thai' => 'required|string|max:255',
            'mo_ta' => 'required|string',
        ]);

        $trangThaiDonHang = TrangThaiDonHang::find($id);

        if (!$trangThaiDonHang) {
            return response()->json(['message' => 'Trạng thái đơn hàng không tồn tại.'], Response::HTTP_NOT_FOUND);
        }

        $trangThaiDonHang->update([
            'ten_trang_thai' => $request->ten_trang_thai,
            'mo_ta' => $request->mo_ta,
        ]);

        return response()->json($trangThaiDonHang);
    }

    /**
     * Xóa trạng thái đơn hàng.
     */
    public function destroy(string $id)
    {
        $trangThaiDonHang = TrangThaiDonHang::find($id);

        if (!$trangThaiDonHang) {
            return response()->json(['message' => 'Trạng thái đơn hàng không tồn tại.'], Response::HTTP_NOT_FOUND);
        }

        $trangThaiDonHang->delete();

        return response()->json(['message' => 'Trạng thái đơn hàng đã bị xóa.'], Response::HTTP_NO_CONTENT);
    }
}
