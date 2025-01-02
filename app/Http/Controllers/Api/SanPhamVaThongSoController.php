<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSanPhamVaThongSoRequest;
use App\Models\SanPhamVaThongSo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SanPhamVaThongSoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SanPhamVaThongSo::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Lấy san_pham_id từ request
        $sanPhamId = $request->input('san_pham_id');

        // Lấy mảng thông số từ request
        $thongSoArray = $request->input('thong_so');

        // Xử lý mảng thông số và tạo các bản ghi mới
        $data = [];
        foreach ($thongSoArray as $thongSo) {
            $data[] = [
                'san_pham_id' => $sanPhamId,
                'thong_so_id' => $thongSo['thong_so_id'],
                'mo_ta' => $thongSo['mo_ta'],
            ];
        }

        // Chèn tất cả bản ghi vào bảng
        SanPhamVaThongSo::created($data);

        return response()->json([
            'message' => 'Sản phẩm và thông số được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Lấy thông tin sản phẩm và thông số cùng với tên danh mục
            $data = SanPhamVaThongSo::with('danhMuc')->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết sản phẩm và thông số id = ' . $id,
                'data' => [
                    'ten_danh_muc_con' => $data->ten_danh_muc_con,
                    'ten_danh_muc' => $data->danhMuc->ten_danh_muc ?? 'Danh mục không tồn tại',
                ]
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm và thông số id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết sản phẩm và thông số: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy chi tiết sản phẩm và thông số',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSanPhamVaThongSoRequest $request, string $id)
    {
        try {
            $data = SanPhamVaThongSo::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật sản phẩm và thông số id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm và thông số id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật sản phẩm và thông số: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm và thông số',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            SanPhamVaThongSo::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm và thông số id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa sản phẩm và thông số: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm và thông số',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
