<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhGiaRequest;
use App\Models\ChiTietDanhGia;
use App\Models\ChiTietDonHang;
use App\Models\DanhGia;
use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\SanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class DanhGiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        $danhGias = DanhGia::with(['sanPham', 'chiTietDanhGias', 'khachHang'])
            ->paginate($numberRow);

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách đánh giá thành công.',
            'data' => $danhGias
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'danh_gia' => 'required|integer|min:1|max:5',
            'binh_luan' => 'nullable|string',
            'image' => 'nullable|array',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Kiểm tra xem khách hàng đã mua sản phẩm chưa
            $daMua = ChiTietDonHang::query()
                ->join('don_hangs', 'chi_tiet_don_hangs.don_hang_id', '=', 'don_hangs.id')
                ->where('don_hangs.khach_hang_id', $request->khach_hang_id)
                ->where('chi_tiet_don_hangs.san_pham_id', $request->san_pham_id)
                ->exists();

            if (!$daMua) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Khách hàng chưa mua sản phẩm này, không thể đánh giá.'
                ], 403);
            }

            // Kiểm tra xem khách hàng đã đánh giá sản phẩm này chưa
            $existingReview = DanhGia::query()
                ->where('khach_hang_id', $request->khach_hang_id)
                ->where('san_pham_id', $request->san_pham_id)
                ->first();

            if ($existingReview) {
                // Nếu đã có đánh giá, xóa đánh giá cũ
                $existingReview->delete();
            }

            // Tạo đánh giá mới
            $danhGia = DanhGia::create([
                'san_pham_id' => $request->san_pham_id,
                'khach_hang_id' => $request->khach_hang_id,
                'danh_gia' => $request->danh_gia,
                'binh_luan' => $request->binh_luan,
                'trang_thai' => 0
            ]);

            // Lưu hình ảnh nếu có
            if ($request->hasFile('image') && is_array($request->file('image'))) {
                $imagePaths = [];

                // Duyệt qua từng tệp hình ảnh và lưu vào thư mục
                foreach ($request->file('image') as $image) {
                    $path = $image->store('chi_tiet_danh_gias', 'public');
                    $imagePaths[] = $path; // Lưu đường dẫn của từng hình ảnh
                    Log::info('Đường dẫn hình ảnh:', ['path' => $path]);

                    // Tạo bản ghi trong cơ sở dữ liệu cho mỗi hình ảnh
                    ChiTietDanhGia::create([
                        'danh_gia_id' => $danhGia->id,
                        'hinh_anh_duong_dan' => $path,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo đánh giá thành công',
                'data' => $danhGia->load('chiTietDanhGias') // Trả về kèm hình ảnh
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DanhGia::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết đánh giá id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy đánh giá id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa đánh giá: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy đánh giá id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = DanhGia::query()->findOrFail($id);
            $data->update([
                'trang_thai' => $request->input('trang_thai'),
            ]);
            return response()->json([
                'message' => 'Cập nhật đánh giá id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật đánh giá: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật đánh giá',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Tìm đánh giá với chi tiết hình ảnh
            $danhGia = DanhGia::with('chiTietDanhGias')->findOrFail($id);

            // Xóa hình ảnh trong chi tiết đánh giá
            foreach ($danhGia->chiTietDanhGias as $chiTiet) {
                // Kiểm tra và xóa hình ảnh vật lý nếu có
                if ($chiTiet->hinh_anh_duong_dan && Storage::exists('public/' . $chiTiet->hinh_anh_duong_dan)) {
                    Storage::delete('public/' . $chiTiet->hinh_anh_duong_dan);
                }
                // Xóa chi tiết đánh giá
                $chiTiet->delete();
            }

            // Xóa đánh giá chính
            $danhGia->delete();

            return response()->json([
                'message' => 'Xóa đánh giá và các chi tiết liên quan thành công.',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đánh giá id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa đánh giá: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa đánh giá.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getDanhGiaBySanPhamId(Request $request, $san_pham_id)
    {
        // Kiểm tra sản phẩm có tồn tại không
        $sanPham = SanPham::find($san_pham_id);

        if (!$sanPham) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        // Lấy tham số page và number_row từ request (với giá trị mặc định là 1 và 5)
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 5);

        // Lấy danh sách đánh giá theo sản phẩm ID và trạng thái với phân trang
        $danhGias = DanhGia::where('san_pham_id', $san_pham_id)
            ->where('trang_thai', 1) // Chỉ lấy đánh giá có trang_thai = 1
            ->with(['chiTietDanhGias', 'khachHang'])
            ->paginate($numberRow);

        // Tính tổng số sao và tổng số lượng đánh giá (chỉ các đánh giá có trang_thai = 1)
        $totalStars = DanhGia::where('san_pham_id', $san_pham_id)
            ->where('trang_thai', 1) // Chỉ tính tổng sao của đánh giá có trang_thai = 1
            ->sum('danh_gia');
        $totalReviews = DanhGia::where('san_pham_id', $san_pham_id)
            ->where('trang_thai', 1) // Chỉ đếm đánh giá có trang_thai = 1
            ->count();

        // Tính điểm sao trung bình
        $averageStars = $totalReviews > 0 ? round($totalStars / $totalReviews, 2) : 0;

        // Trả về kết quả
        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách đánh giá thành công.',
            'data' => $danhGias, // Dữ liệu phân trang
            'summary' => [
                'tong_sao' => $totalStars,
                'trung_binh_sao' => $averageStars,
                'tong_danh_gia' => $totalReviews
            ]
        ]);
    }

    public function getDanhGiaByKhachHangId(Request $request, $khach_hang_id)
    {
        // Kiểm tra khách hàng có tồn tại không
        $khachHang = KhachHang::find($khach_hang_id);

        if (!$khachHang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Khách hàng không tồn tại.'
            ], 404);
        }

        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        $danhGias = DanhGia::where('khach_hang_id', $khach_hang_id)
            ->with(['sanPham', 'chiTietDanhGias'])
            ->paginate($numberRow);

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách đánh giá thành công.',
            'data' => $danhGias
        ]);
    }

}
