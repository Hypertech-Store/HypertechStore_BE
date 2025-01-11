<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKhachHangRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KhachHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllKhachHang(): \Illuminate\Http\JsonResponse
    {
        // Lấy tất cả các bản ghi từ bảng QuanTriVien
        $khachHangs = KhachHang::all();

        // Trả về kết quả dưới dạng JSON
        return response()->json($khachHangs, 200);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|min:8',
        ]);

        // Tìm người dùng theo email
        $khachHang = KhachHang::where('email', $request->email)->first();

        // Kiểm tra thông tin đăng nhập
        if ($khachHang && Hash::check($request->mat_khau, $khachHang->mat_khau)) {

            Auth::login($khachHang);

            return response()->json([
                'message' => 'Đăng nhập thành công!',
                'user' => $khachHang
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'error' => 'Thông tin đăng nhập không chính xác.'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    /**
     * Xử lý đăng ký khách hàng mới.
     */
    public function register(StoreKhachHangRequest $request)
    {
        // Kiểm tra nếu email hoặc tên người dùng đã tồn tại
        $existingUser = KhachHang::query()
            ->where('ten_nguoi_dung', $request->ten_nguoi_dung)
            ->orWhere('email', $request->email)
            ->first();

        if ($existingUser) {
            $errorMessage = $existingUser->email === $request->email
                ? 'Email đã tồn tại trên hệ thống.'
                : 'Tên người dùng đã tồn tại trên hệ thống.';
            return response()->json([
                'message' => $errorMessage,
            ], Response::HTTP_BAD_REQUEST);
        }

        // Tạo mới khách hàng với trạng thái mặc định là 1
        $khachHang = KhachHang::query()->create([
            'ten_nguoi_dung' => $request->ten_nguoi_dung,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'trang_thai' => 1, // Trạng thái mặc định là 1 (kích hoạt)
        ]);

        // Trả về phản hồi JSON
        return response()->json([
            'message' => 'Đăng ký thành công!',
            'data' => $khachHang,
        ], Response::HTTP_CREATED);
    }



    /**
     * Xử lý đăng xuất khách hàng.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Đăng xuất thành công'
        ], 200);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $khachHang = KhachHang::find($id);

        if (!$khachHang) {
            return response()->json([
                'error' => 'Không tìm thấy người dùng'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Lấy thông tin người dùng thành công!',
            'user' => $khachHang
        ], Response::HTTP_OK);
    }

    // Phương thức cập nhật thông tin người dùng theo ID
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $khachHang = KhachHang::find($id);

        if (!$khachHang) {
            return response()->json([
                'error' => 'Không tìm thấy người dùng'
            ], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'ten_nguoi_dung' => 'nullable|string',
            'ho_ten' => 'nullable|string',
            'dien_thoai' => 'nullable|string',
            'dia_chi' => 'nullable|string',
            'gioi_tinh' => 'nullable|string|in:Male,Female',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'ngay_sinh' => 'nullable|date',
            'old_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|min:8'
        ]);

        if (!$khachHang) {
            return response()->json(['message' => 'Không tìm thấy hình ảnh sản phẩm nào'], 404);
        }

        // Nếu có ảnh mới được tải lên, lưu ảnh và cập nhật đường dẫn
        if ($request->hasFile('image')) {
            if ($khachHang->duong_dan_hinh_anh && Storage::exists('public/' . $khachHang->duong_dan_hinh_anh)) {
                Storage::delete('public/' . $khachHang->duong_dan_hinh_anh);
            }
            $path =  $request->file('image')->store('khach_hangs', 'public');
            Log::info('Đường dẫn hình ảnh mới:', ['path' => $path]);
            $khachHang->update([
                'hinh_anh' => $path,
            ]);
        }
        // Kiểm tra mật khẩu cũ nếu truyền vào
        if (isset($validatedData['old_password']) && isset($validatedData['new_password'])) {
            if (!Hash::check($validatedData['old_password'], $khachHang->mat_khau)) {
                return response()->json([
                    'error' => 'Mật khẩu cũ không đúng'
                ], Response::HTTP_UNAUTHORIZED);
            }
            // Mã hóa mật khẩu mới trước khi cập nhật
            $validatedData['mat_khau'] = Hash::make($validatedData['new_password']);
            unset($validatedData['old_password'], $validatedData['new_password']);
        }

        $khachHang->update($validatedData);

        return response()->json([
            'message' => 'Cập nhật thông tin người dùng thành công!',
            'data' => $khachHang
        ], Response::HTTP_OK);
    }

    public function updateStatus(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'khach_hang_id' => 'required|integer|exists:khach_hangs,id', // Kiểm tra khach_hang_id tồn tại
            'trang_thai'    => 'required|boolean', // Giá trị trạng thái phải là 0 hoặc 1
        ]);


        // Tìm khách hàng theo ID
        $khachHang = KhachHang::find($validated['khach_hang_id']);

        // Cập nhật trạng thái
        $khachHang->trang_thai = $validated['trang_thai'];
        $khachHang->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => [
                'khach_hang_id' => $khachHang->id,
                'trang_thai' => $khachHang->trang_thai,
            ],
        ]);
    }

    // Gửi email đặt lại mật khẩu
    public function quenMatKhau(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $khachHang = KhachHang::where('email', $request->email)->first();

        if (!$khachHang) {
            return response()->json(['message' => 'Email không tồn tại trong hệ thống.'], 404);
        }

        // Tạo token ngẫu nhiên
        $token = Str::random(60);
        $khachHang->update(['mat_khau_reset_token' => $token]);

        // Gửi email đặt lại mật khẩu
        Mail::send(
            'emails.quen-mat-khau',
            [
                'token' => $token,
                'name' => $khachHang->ho_ten,
            ],
            function ($message) use ($khachHang) {
                $message->to($khachHang->email)
                    ->subject('Yêu cầu đặt lại mật khẩu');
            }
        );

        return response()->json(['message' => 'Đã gửi email đặt lại mật khẩu.']);
    }

    // Đặt lại mật khẩu
    public function datLaiMatKhau(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'mat_khau_moi' => 'required|min:6|confirmed',
        ]);

        $khachHang = KhachHang::where('mat_khau_reset_token', $request->token)->first();

        if (!$khachHang) {
            return response()->json(['message' => 'Token không hợp lệ.'], 400);
        }

        // Cập nhật mật khẩu mới
        $khachHang->update([
            'mat_khau' => Hash::make($request->mat_khau_moi),
            'mat_khau_reset_token' => null,
        ]);

        return response()->json([
            'message' => 'Đặt lại mật khẩu thành công.',
            'user' => $khachHang->only(['id', 'email', 'ho_ten']),
        ]);
    }


    public function getAllUsers(): \Illuminate\Http\JsonResponse
    {
        // Lấy tất cả người dùng từ cơ sở dữ liệu
        $users = KhachHang::all();

        // Kiểm tra nếu không có người dùng nào
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'Không có người dùng nào',
            ], 404);
        }

        // Trả về danh sách người dùng
        return response()->json(
            [
                'message' => 'Lấy danh sách người dùng thành công',
                'data' => $users,
            ],
            200
        );
    }
}
