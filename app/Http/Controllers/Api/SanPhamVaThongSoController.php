<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSanPhamVaThongSoRequest;
use App\Models\SanPhamVaThongSo;
use App\Models\SanPham;
use App\Models\ThongSo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SanPhamVaThongSoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Phân trang với 10 bản ghi mỗi trang và tải dữ liệu quan hệ
        $data = SanPhamVaThongSo::with(['sanPham:id,ten_san_pham', 'thongSo:id,ten_thong_so']) // Lấy thêm tên sản phẩm và thông số
            ->paginate(10);

        // Sử dụng map để xử lý từng phần tử
        $data->setCollection(
            $data->getCollection()->map(function ($item) {
                // Chỉ lấy tên sản phẩm và tên thông số
                $item->ten_san_pham = $item->sanPham->ten_san_pham ?? null; // Thêm tên sản phẩm
                $item->ten_thong_so = $item->thongSo->ten_thong_so ?? null; // Thêm tên thông số

                unset($item->sanPham);
                unset($item->thongSo);

                return $item;
            })
        );

        return response()->json($data); // Trả về JSON phân trang, bao gồm data và last_page
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Lấy san_pham_id từ request
        $sanPhamId = $request->input('product_id'); // sử dụng 'product_id' thay cho 'san_pham_id'

        // Lấy mảng thông số từ request
        $thongSoArray = $request->input('parameters'); // sử dụng 'parameters' thay cho 'thong_so'

        // Xử lý mảng thông số và tạo các bản ghi mới
        $data = [];
        foreach ($thongSoArray as $thongSo) {
            // Chèn bản ghi vào bảng
            $insertedData = SanPhamVaThongSo::create([
                'san_pham_id' => $sanPhamId,
                'thong_so_id' => $thongSo['spec_id'],
                'mo_ta' => $thongSo['description'],
            ]);

            // Lấy thông tin tên sản phẩm và tên thông số
            $sanPham = $insertedData->sanPham;
            $thongSoData = $insertedData->thongSo;

            // Thêm vào mảng dữ liệu trả về
            $data[] = [
                'san_pham_id' => $sanPham->id,
                'ten_san_pham' => $sanPham->ten_san_pham,
                'thong_so_id' => $thongSoData->id,
                'ten_thong_so' => $thongSoData->ten_thong_so,
                'mo_ta' => $insertedData->mo_ta, // Mô tả thông số
            ];
        }

        return response()->json([
            'message' => 'Sản phẩm và thông số được tạo thành công!',
            'data' => $data,
        ], Response::HTTP_CREATED);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Lấy thông tin sản phẩm và thông số cùng với tên danh mục
            $data = SanPhamVaThongSo::with('sanPham')->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết sản phẩm và thông số được lấy thành công.',
                'data' => $data
            ]);
        } catch (ModelNotFoundException $th) {
            Log::warning("Không tìm thấy sản phẩm và thông số id = {$id}");
            return response()->json([
                'message' => "Không tìm thấy sản phẩm và thông số có id = {$id}",
                'error' => $th->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy chi tiết sản phẩm và thông số: {$e->getMessage()}");
            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy chi tiết sản phẩm và thông số.',
                'error' => $e->getMessage(),
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
