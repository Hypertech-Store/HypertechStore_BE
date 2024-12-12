<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHinhAnhSanPhamRequest;
use App\Models\HinhAnhSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
            if ($request->hasFile('image') && is_array($request->file('image'))) {

                $imagePaths = [];

                // Duyệt qua từng tệp hình ảnh và lưu vào thư mục
                foreach ($request->file('image') as $image) {
                    $path = $image->store('hinh_anh_san_phams', 'public');
                    $imagePaths[] = $path; // Lưu đường dẫn của từng hình ảnh
                    Log::info('Đường dẫn hình ảnh:', ['path' => $path]);

                    // Tạo bản ghi trong cơ sở dữ liệu cho mỗi hình ảnh
                    HinhAnhSanPham::create([
                        'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => $validated['lien_ket_bien_the_va_gia_tri_thuoc_tinh_id'],
                        'duong_dan_hinh_anh' => $path,
                    ]);
                }

                return response()->json([
                    'message' => 'Hình ảnh sản phẩm được tạo thành công!',
                    'data' => $imagePaths
                ], Response::HTTP_CREATED);
            }

            return response()->json([
                'message' => 'Không có hình ảnh nào được tải lên.'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo hình ảnh sản phẩm:', ['error' => $e->getMessage()]);
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
            $data = HinhAnhSanPham::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết hình ảnh sản phẩm id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa hình ảnh sản phẩm: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => 'nullable|integer|exists:lien_ket_bien_the_va_gia_tri_thuoc_tinhs,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
            $data = HinhAnhSanPham::find($id);

            if (!$data) {
                return response()->json(['message' => 'Không tìm thấy hình ảnh sản phẩm nào'], 404);
            }

            // Nếu có ảnh mới được tải lên, lưu ảnh và cập nhật đường dẫn
            if ($request->hasFile('image')) {
                if ($data->duong_dan_hinh_anh && Storage::exists('public/' . $data->duong_dan_hinh_anh)) {
                    Storage::delete('public/' . $data->duong_dan_hinh_anh);
                }
                $path =  $request->file('image')->store('hinh_anh_san_phams', 'public');
                Log::info('Đường dẫn hình ảnh mới:', ['path' => $path]);
                $data->update([
                    'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => $validated['lien_ket_bien_the_va_gia_tri_thuoc_tinh_id'],
                    'duong_dan_hinh_anh' => $path,
                ]);
            } else {
                // Nếu không có ảnh mới, chỉ cập nhật các trường khác
                $data->update([
                    'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => $validated['lien_ket_bien_the_va_gia_tri_thuoc_tinh_id'],
                ]);
            }

            return response()->json($data, 200);

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
            HinhAnhSanPham::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình ảnh sản phẩm id = ' . $id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa hình ảnh sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa hình ảnh sản phẩm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
