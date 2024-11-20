<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBienTheSanPhamRequest;
use App\Models\BienTheSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BienTheSanPhamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BienTheSanPham::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBienTheSanPhamRequest $request)
    {

        $data = BienTheSanPham::query()->create($request->all());
        return response()->json([
            'message' => 'Biến thể sản phẩm được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = BienTheSanPham::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết Biến thể sản phẩm id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy biến thể sản phẩm id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa biến thể sản phẩm: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy biến thể sản phẩm id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBienTheSanPhamRequest $request, string $id)
    {
        try {
            $data = BienTheSanPham::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật biến thể sản phẩm id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy biến thể sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật biến thể sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật biến thể sản phẩm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            BienTheSanPham::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy biến thể sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa biến thể sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa biến thể sản phẩm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getBienTheBySanPhamId($san_pham_id): JsonResponse
    {
        $data = BienTheSanPham::where('san_pham_id', $san_pham_id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy biến thể cho sản phẩm này.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }
    public function getBienTheByAttributes(Request $request): JsonResponse
    {
        // Log giá trị đầu vào
        Log::info('Dữ liệu đầu vào:', [
            'ten_bien_the' => $request->ten_bien_the,
            'gia_tri_bien_the' => $request->gia_tri_bien_the
        ]);

        // Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'ten_bien_the' => 'required|string|max:255',
            'gia_tri_bien_the' => 'required|string|max:255',
        ]);

        // Lấy dữ liệu từ POST request sau khi đã validate
        $ten_bien_the = $validatedData['ten_bien_the'];
        $gia_tri_bien_the = $validatedData['gia_tri_bien_the'];

        // Tìm biến thể với các thuộc tính này
        $bienThe = BienTheSanPham::where('ten_bien_the', $ten_bien_the)
            ->where('gia_tri_bien_the', $gia_tri_bien_the)
            ->first();

        // Kiểm tra nếu không tìm thấy biến thể
        if (!$bienThe) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy biến thể với tên và giá trị này.'
            ], 404);
        }

        Log::info('Kết quả tìm kiếm:', [
            'bien_the_san_pham' => $bienThe
        ]);

        // Trả về biến thể nếu tìm thấy
        return response()->json([
            'success' => true,
            'data' => $bienThe
        ], 200);
    }
}
