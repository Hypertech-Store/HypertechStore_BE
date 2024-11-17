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
            'role' => 'required|string',
            'is_active' => 'boolean',
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
            'role' => 'string|nullable',
            'is_active' => 'boolean|nullable',
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

        // Đảo trạng thái is_active
        $quanTriVien->trang_thai = !$quanTriVien->trang_thai;
        $quanTriVien->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $quanTriVien,
        ], 200);
    }
}
