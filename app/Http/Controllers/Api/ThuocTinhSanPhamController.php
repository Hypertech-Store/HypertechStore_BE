<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreThuocTinhSanPhamRequest;
use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ThuocTinhSanPhamController extends Controller
{
    public function index()
    {
        $data = ThuocTinhSanPham::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThuocTinhSanPhamRequest $request)
    {

        $data = ThuocTinhSanPham::query()->create($request->all());

        return response()->json([
            'message' => 'Thuộc tính sản phẩm được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = ThuocTinhSanPham::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết thuộc tính sản phẩm id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy thuộc tính sản phẩm id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa thuộc tính sản phẩm: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy thuộc tính sản phẩm id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreThuocTinhSanPhamRequest $request, string $id)
    {
        try {
            $data = ThuocTinhSanPham::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật thuộc tính sản phẩm id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thuộc tính sản phẩm id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật thuộc tính sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật thuộc tính sản phẩm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            ThuocTinhSanPham::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thuộc tính sản phẩm id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa thuộc tính sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa thuộc tính sản phẩm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function layGiaTriThuocTinhTheoThuocTinh()
    {
        $data = ThuocTinhSanPham::with('giaTriThuocTinh')->get();

        return response()->json($data);
    }
}
