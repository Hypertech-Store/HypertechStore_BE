<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreThongSoRequest;
use App\Models\ThongSo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ThongSoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ThongSo::with('danhMuc')->paginate(10);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThongSoRequest $request)
    {
        // Kiểm tra nếu không có dữ liệu thong_so_list thì trả về lỗi
        $thongSoList = $request->input('thong_so_list');
        if (empty($thongSoList)) {
            return response()->json([
                'message' => 'Danh sách thông số không thể để trống!'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Mảng chứa các thông số đã được tạo thành công
        $createdThongSos = [];

        // Lặp qua từng thông số trong danh sách và lưu vào cơ sở dữ liệu
        foreach ($thongSoList as $thongSoData) {
            // Kiểm tra xem các trường thông số có hợp lệ không
            if (empty($thongSoData['danh_muc_id']) || empty($thongSoData['ten_thong_so'])) {
                return response()->json([
                    'message' => 'Danh mục và tên thông số là bắt buộc!',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Tạo thông số mới
            $thongSo = ThongSo::create([
                'danh_muc_id' => $thongSoData['danh_muc_id'],
                'ten_thong_so' => $thongSoData['ten_thong_so'],
                'mo_ta' => $thongSoData['mo_ta'] ?? null, // Cho phép để trống mô tả
            ]);

            // Lưu thông số đã tạo vào mảng
            $createdThongSos[] = $thongSo;
        }

        // Trả về phản hồi JSON với thông tin các thông số đã được tạo
        return response()->json([
            'message' => 'Thông số được tạo thành công!',
            'data' => $createdThongSos
        ], Response::HTTP_CREATED);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = ThongSo::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết thông số id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy thông số id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa thông số: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy thông số id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreThongSoRequest $request, string $id)
    {
        try {
            $data = ThongSo::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật thông số id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông số id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật thông số: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật thông số',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            ThongSo::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông số id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa thông số: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa thông số',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
