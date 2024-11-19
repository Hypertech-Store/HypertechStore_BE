<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuanTriVien;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'dia_chi' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:15',
        ]);

        $validated['mat_khau'] = Hash::make($validated['mat_khau']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('quan_tri_viens', 'public');
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);


        }
        $quanTriVien = QuanTriVien::create([
            'ten_dang_nhap' => $validated['ten_dang_nhap'],
            'mat_khau' => $validated['mat_khau'],
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'trang_thai' => $validated['trang_thai'],
            'dia_chi' => $validated['dia_chi'] ?? null,
            'so_dien_thoai' => $validated['so_dien_thoai'] ?? null,
            'anh_nguoi_dung' => $path,
        ]);
        Log::info('QuanTriVien vừa tạo:', $quanTriVien->toArray());

        return response()->json([
            'message' => 'Quản trị viên đã được thêm',
            'data' => $quanTriVien
        ], Response::HTTP_CREATED);
    }

    // Sửa quản trị viên
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = QuanTriVien::findOrFail($id);

            $validated = $request->validate([
                'ten_dang_nhap' => 'string|unique:quan_tri_viens,ten_dang_nhap,' . $id,
                'mat_khau' => 'string|min:6|nullable',
                'ho_ten' => 'string|max:255|nullable',
                'email' => 'email|unique:quan_tri_viens,email,' . $id,
                'role' => 'int|nullable',
                'trang_thai' => 'boolean|nullable',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'dia_chi' => 'nullable|string|max:255',
                'so_dien_thoai' => 'nullable|string|max:15',
            ]);

            // Mã hóa mật khẩu nếu có
            if (!empty($validated['mat_khau'])) {
                $validated['mat_khau'] = Hash::make($validated['mat_khau']);
            }

            // Nếu có ảnh mới, lưu ảnh và cập nhật đường dẫn
            if ($request->hasFile('image')) {
                if ($data->anh_nguoi_dung && Storage::exists('public/' . $data->anh_nguoi_dung)) {
                    Storage::delete('public/' . $data->anh_nguoi_dung);
                }

                $path = $request->file('image')->store('quan_tri_viens', 'public');
                $validated['anh_nguoi_dung'] = $path;
            } else {
                // Nếu không có ảnh mới, giữ nguyên ảnh cũ
                $validated['anh_nguoi_dung'] = $data->anh_nguoi_dung;
            }

            // Kết hợp dữ liệu cũ và dữ liệu mới
            $updatedData = array_merge($data->toArray(), $validated);

            // Cập nhật dữ liệu
            $data->update($updatedData);

            return response()->json($data, 200);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật quản trị viên: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật quản trị viên'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|min:6',
        ]);

        // Tìm người dùng theo email
        $quantrivien = QuanTriVien::where('email', $request->email)->first();

        // Kiểm tra thông tin đăng nhập
        if ($quantrivien && Hash::check($request->mat_khau, $quantrivien->mat_khau)) {

            Auth::login($quantrivien);

            return response()->json([
                'message' => 'Đăng nhập thành công!',
                'quantrivien' => $quantrivien
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'error' => 'Thông tin đăng nhập không chính xác.'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
