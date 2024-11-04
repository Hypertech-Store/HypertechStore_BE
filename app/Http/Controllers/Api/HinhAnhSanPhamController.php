<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHinhAnhSanPhamRequest;
use App\Models\HinhAnhSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HinhAnhSanPhamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = HinhAnhSanPham::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHinhAnhSanPhamRequest $request)
    {
        try {
            $validated = $request->validated();
            Log::info('Validated data:', $validated); // Ghi log dữ liệu đã xác thực

            if ($request->hasFile('image')) {
                $path = Storage::put('hinh_anh_san_phams', $request->file("image"));
                Log::info('Đường dẫn hình ảnh:', ['path' => $path]);

                $data = HinhAnhSanPham::create([
                    'san_pham_id' => $validated['san_pham_id'],
                    'duong_dan_hinh_anh' => $path,
                ]);

                return response()->json([
                    'message' => 'Hình ảnh sản phẩm được tạo thành công!',
                    'data' => $data
                ], Response::HTTP_CREATED);
            }

            return response()->json([
                'message' => 'Không có hình ảnh nào được tải lên.'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi tạo hình ảnh sản phẩm'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = HinhAnhSanPham::findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết hình ảnh sản phẩm id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi truy xuất hình ảnh sản phẩm: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi truy xuất hình ảnh sản phẩm'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreHinhAnhSanPhamRequest $request, string $id)
    {
        try {
            $validated = $request->validated();
            $data = HinhAnhSanPham::find($id);
            if (!$data) {
                return response()->json(['message' => 'Không tìm thấy hình ảnh sản phẩm nào'], 404);
            }

        if ($request->hasFile('image')) {
            $path = Storage::put('hinh_anh_san_phams', $request->file("image"));
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);

            $data->update([
                'san_pham_id' => $validated['san_pham_id'],
                'duong_dan_hinh_anh' => $path,
            ]);

            return response()->json($data, 200);
        }
        return response()->json([
            'message' => 'Không có hình ảnh nào được tải lên.'
        ], Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật hình ảnh sản phẩm: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật hình ảnh sản phẩm'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            $data = HinhAnhSanPham::findOrFail($id);

            // Delete the image file from storage
            if ($data->duong_dan_hinh_anh) {
                Storage::disk('public')->delete($data->duong_dan_hinh_anh);
            }

            $data->delete();

            return response()->json([
                'message' => 'Xóa hình ảnh sản phẩm thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa hình ảnh sản phẩm: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa hình ảnh sản phẩm'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
