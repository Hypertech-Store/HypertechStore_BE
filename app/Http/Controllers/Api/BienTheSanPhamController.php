<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBienTheSanPhamRequest;
use App\Models\BienTheSanPham;
use App\Models\GiaTriThuocTinh;
use App\Models\LienKetBienTheVaGiaTriThuocTinh;
use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $attributes = $request->input('attributes', []); // Lấy mảng thuộc tính từ yêu cầu

        // Kiểm tra nếu không có thuộc tính nào được truyền vào
        if (empty($attributes)) {
            return response()->json([
                'message' => 'Không có ID giá trị thuộc tính nào được cung cấp.'
            ], 400);
        }

        // Tìm các `bien_the_san_pham_id` khớp với tất cả các `gia_tri_thuoc_tinh_id` sử dụng Model
        $bienTheIds = LienKetBienTheVaGiaTriThuocTinh::whereIn('gia_tri_thuoc_tinh_id', $attributes)
            ->groupBy('bien_the_san_pham_id')
            ->havingRaw('COUNT(DISTINCT gia_tri_thuoc_tinh_id) = ?', [count($attributes)]) // Đảm bảo rằng số lượng thuộc tính cần khớp
            ->pluck('bien_the_san_pham_id');

        $bienTheSanPhams = BienTheSanPham::query()->findOrFail($bienTheIds);

        // Kiểm tra nếu không tìm thấy kết quả
        if ($bienTheIds->isEmpty()) {
            return response()->json([
                'message' => 'Không có biến thể sản phẩm nào khớp với các giá trị thuộc tính đã chọn.'
            ], 404);
        }

        // Lấy tên các giá trị thuộc tính sử dụng Model
        $tenGiaTriThuocTins = GiaTriThuocTinh::whereIn('id', $attributes)
            ->pluck('ten_gia_tri', 'id');

        // Trả về danh sách ID biến thể sản phẩm cùng với tên giá trị thuộc tính
        return response()->json([
            'bien_the_san_pham' => $bienTheSanPhams,
            'attributes' => $attributes,
            'mang_gia_tri_thuoc_tinh' => $tenGiaTriThuocTins
        ], 200);
    }

}
