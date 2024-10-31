<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SanPhamController extends Controller
{
    public function getAllProduct(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 10
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($sanPhams);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'duong_dan_anh' => 'nullable|string',
            'luot_Xem' => 'integer|min:0',
        ]);

        $sanPham = SanPham::create($request->all());
        return response()->json($sanPham, 201);
    }

    public function getDetail($id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }
        return response()->json($sanPham, 200);
    }

    // Cập nhật sản phẩm
    public function updateProduct(Request $request, $id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $request->validate([
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'duong_dan_anh' => 'nullable|string',
            'luot_Xem' => 'integer|min:0',
        ]);

        $sanPham->update($request->all());
        return response()->json($sanPham, 200);
    }

    // Xóa sản phẩm
    public function deleteProduct($id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $sanPham->delete();
        return response()->json(['message' => 'Xóa sản phẩm thành công'], 200);
    }
}
