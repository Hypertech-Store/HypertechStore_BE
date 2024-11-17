<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuanTriVien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class QuanTriVienController extends Controller
{
    // Thêm quản trị viên
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ten_dang_nhap' => 'required|string|unique:quan_tri_viens,ten_dang_nhap',
            'mat_khau' => 'required|string|min:6',
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:quan_tri_viens,email',
            'role' => 'required|int',
            'trang_thai' => 'boolean',
            'anh_nguoi_dung' => 'nullable|string',
            'dia_chi' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:15',
        ]);

        $validated['mat_khau'] = Hash::make($validated['mat_khau']);
        $quanTriVien = QuanTriVien::create($validated);

        return response()->json(['data' => $quanTriVien], 201);
    }

    // Sửa quản trị viên
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $quanTriVien = QuanTriVien::findOrFail($id);

        $validated = $request->validate([
            'ten_dang_nhap' => 'string|unique:quan_tri_viens,ten_dang_nhap,' . $id,
            'mat_khau' => 'string|min:6|nullable',
            'ho_ten' => 'string|max:255|nullable',
            'email' => 'email|unique:quan_tri_viens,email,' . $id,
            'role' => 'int|nullable',
            'trang_thai' => 'boolean|nullable',
            'anh_nguoi_dung' => 'nullable|string',
            'dia_chi' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:15',
        ]);

        if (!empty($validated['mat_khau'])) {
            $validated['mat_khau'] = Hash::make($validated['mat_khau']);
        }

        $quanTriVien->update($validated);

        return response()->json(['data' => $quanTriVien], 200);
    }

    // Xóa quản trị viên
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $quanTriVien = QuanTriVien::findOrFail($id);
        $quanTriVien->delete();

        return response()->json(['message' => 'Quản trị viên đã được xóa'], 200);
    }

    public function toggleActive($id): \Illuminate\Http\JsonResponse
    {
        $quanTriVien = QuanTriVien::findOrFail($id);

        // Đảo trạng thái trang_thai
        $quanTriVien->trang_thai = !$quanTriVien->trang_thai;
        $quanTriVien->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $quanTriVien,
        ], 200);
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $pageSize = $request->query('per_page', 10); // Số bản ghi mỗi trang, mặc định là 10
        $quanTriViens = QuanTriVien::paginate($pageSize);

        return response()->json($quanTriViens, 200);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $quanTriVien = QuanTriVien::findOrFail($id);

        return response()->json($quanTriVien, 200);
    }

}
