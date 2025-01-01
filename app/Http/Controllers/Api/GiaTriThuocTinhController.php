<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGiaTriThuocTinhRequest;
use App\Models\GiaTriThuocTinh;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GiaTriThuocTinhController extends Controller
{
    public function index(Request $request)
    {
        // Lấy số lượng bản ghi mỗi trang từ tham số hoặc mặc định là 5
        $perPage = $request->get('per_page', 5); // Mặc định mỗi trang có 5 mục

        $data = GiaTriThuocTinh::query()
            ->with('ThuocTinhSanPham:id,ten_thuoc_tinh') // Lấy các trường id và ten_thuoc_tinh
            ->paginate($perPage) // Phân trang
            ->onEachSide(1); // Hiển thị 1 trang ở hai bên trang hiện tại (tuỳ chọn)

        // Xử lý dữ liệu
        $data->getCollection()->transform(function ($item) {
            // Lấy thông tin `ten_thuoc_tinh` và đẩy vào dữ liệu chính
            $item->ten_thuoc_tinh = $item->ThuocTinhSanPham->ten_thuoc_tinh;
            unset($item->ThuocTinhSanPham); // Loại bỏ đối tượng 'ThuocTinhSanPham' khỏi kết quả
            return $item;
        });

        return response()->json($data);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGiaTriThuocTinhRequest $request)
    {

        $data = GiaTriThuocTinh::query()->create($request->all());

        return response()->json([
            'message' => 'Giá trị thuộc tính  được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = GiaTriThuocTinh::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết giá trị thuộc tính  id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy giá trị thuộc tính  id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa giá trị thuộc tính : ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính  id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreGiaTriThuocTinhRequest $request, string $id)
    {
        try {
            $data = GiaTriThuocTinh::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật giá trị thuộc tính  id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính  id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật giá trị thuộc tính : ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật giá trị thuộc tính ',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            GiaTriThuocTinh::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy giá trị thuộc tính  id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa giá trị thuộc tính : ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa giá trị thuộc tính ',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function layGiaTriThuocTinhTheoThuocTinh()
    {
        $data = GiaTriThuocTinh::with('thuocTinhSanPham')->get();

        return response()->json($data);
    }
}
