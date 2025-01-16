<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrangThaiDonHang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrangThaiDonHangController extends Controller
{
    public function getAll()
    {
        $trangThaiDonHangs = TrangThaiDonHang::all();
        return response()->json($trangThaiDonHangs);
    }


    public function index(Request $request)
    {
        try {
            // Số lượng bản ghi mỗi trang
            $perPage = 10; // Example: you can change it as needed or make it dynamic with $request

            // Lấy tất cả trạng thái đơn hàng với phân trang
            $trangThaiDonHangs = TrangThaiDonHang::paginate($perPage);

            // Trả về kết quả phân trang, bao gồm dữ liệu và thông tin phân trang
            return response()->json([
                'current_page' => $trangThaiDonHangs->currentPage(),
                'last_page' => $trangThaiDonHangs->lastPage(),
                'per_page' => $trangThaiDonHangs->perPage(),
                'total' => $trangThaiDonHangs->total(),
                'data' => $trangThaiDonHangs->items(), // Dữ liệu trạng thái đơn hàng
            ]);
        } catch (\Exception $e) {
            // Trả về lỗi nếu có sự cố
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình truy vấn.',
                'error' => $e->getMessage(),
            ], 500);
        }
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


    public function updateStatus(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'trang_thai_id' => 'required|integer|exists:trang_thai_don_hangs,id', // Kiểm tra trang_thai_id tồn tại
            'trang_thai'    => 'required|boolean', // Giá trị trạng thái phải là 0 hoặc 1
        ]);

        // Tìm khách hàng theo ID
        $trangThaiDonHang = TrangThaiDonHang::find($validated['trang_thai_id']);

        if (!$trangThaiDonHang) {
            return response()->json([
                'success' => false,
                'message' => 'Trạng thái không tìm thấy.',
            ], 404);
        }

        // Cập nhật trạng thái
        $trangThaiDonHang->trang_thai = $validated['trang_thai'];
        $trangThaiDonHang->save();

        // Trả về dữ liệu phương thức vận chuyển sau khi cập nhật
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => [
                'trang_thai_id' => $trangThaiDonHang->id,
                'trang_thai' => $trangThaiDonHang->trang_thai,
                // Thêm bất kỳ trường nào khác bạn cần đưa vào response
            ],
        ]);
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
