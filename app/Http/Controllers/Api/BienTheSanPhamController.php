<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBienTheSanPhamRequest;
use App\Models\BienTheSanPham;
use App\Models\ChiTietDonHang;
use App\Models\GiaTriThuocTinh;
use App\Models\HinhAnhSanPham;
use App\Models\LienKetBienTheVaGiaTriThuocTinh;
use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BienTheSanPhamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các biến thể sản phẩm
        $data = BienTheSanPham::query()->get();

        // Duyệt qua từng biến thể sản phẩm để bổ sung thông tin liên quan
        $result = $data->map(function ($item) {
            // Lấy tất cả các liên kết của biến thể sản phẩm
            $lienKetBienThe = LienKetBienTheVaGiaTriThuocTinh::where('bien_the_san_pham_id', $item->id)
                ->get()
                ->map(function ($lienKet) {
                    // Lấy `ten_gia_tri` từ bảng GiaTriThuocTinh
                    $tenGiaTri = GiaTriThuocTinh::where('id', $lienKet->gia_tri_thuoc_tinh_id)->value('ten_gia_tri');

                    return [
                        'id' => $lienKet->id,
                        'gia_tri_thuoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                        'ten_gia_tri' => $tenGiaTri,
                    ];
                });

            // Duyệt qua các liên kết và lấy hình ảnh sản phẩm tương ứng
            $hinhAnhSanPham = $lienKetBienThe->map(function ($lienKet) {
                return HinhAnhSanPham::where('lien_ket_bien_the_va_gia_tri_thuoc_tinh_id', $lienKet['id'])->get();
            });

            // Trả về dữ liệu trong một mảng duy nhất
            return [
                'bienTheSanPham' => $item,
                'lienKetBienThe' => $lienKetBienThe,
                'hinhAnhSanPham' => $hinhAnhSanPham->flatten(), // Đưa các hình ảnh vào một mảng phẳng
            ];
        });

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($result);
    }

    public function getBienThePaginate()
    {
        // Lấy tất cả các biến thể sản phẩm kèm các liên kết và giá trị liên quan
        $data = BienTheSanPham::with("sanPham")
            ->paginate(10);

        // Duyệt qua từng biến thể sản phẩm để bổ sung thông tin cần thiết
        $result = $data->map(function ($item) {
            // Lấy tất cả các liên kết của biến thể sản phẩm
            $lienKetBienThe = LienKetBienTheVaGiaTriThuocTinh::where('bien_the_san_pham_id', $item->id)
                ->get()
                ->map(function ($lienKet) {
                    // Lấy `ten_gia_tri` từ bảng GiaTriThuocTinh
                    $tenGiaTri = GiaTriThuocTinh::where('id', $lienKet->gia_tri_thuoc_tinh_id)->value('ten_gia_tri');

                    return [
                        'id' => $lienKet->id,
                        'gia_tri_thuoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                        'ten_gia_tri' => $tenGiaTri,
                    ];
                });

            // Duyệt qua các liên kết và lấy hình ảnh sản phẩm tương ứng
            $hinhAnhSanPham = $lienKetBienThe->map(function ($lienKet) {
                return HinhAnhSanPham::where('lien_ket_bien_the_va_gia_tri_thuoc_tinh_id', $lienKet['id'])->get();
            });

            // Trả về dữ liệu trong một mảng duy nhất
            return [
                'bienTheSanPham' => $item,
                'lienKetBienThe' => $lienKetBienThe,
                'hinhAnhSanPham' => $hinhAnhSanPham->flatten(), // Đưa các hình ảnh vào một mảng phẳng
            ];
        });
        // Trả về dữ liệu với thông tin phân trang đầy đủ
        return response()->json([
            'data' => $result,
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
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
            // Tìm biến thể sản phẩm cần cập nhật
            $data = BienTheSanPham::query()->findOrFail($id);

            // Cập nhật biến thể sản phẩm (trừ trường 'image')
            $data->update($request->except('image'));

            // Lấy tất cả các id liên kết có bien_the_san_pham_id tương ứng
            $lienKetBienThe = LienKetBienTheVaGiaTriThuocTinh::where('bien_the_san_pham_id', $data->id)->get();

            // Lấy mảng gia_tri_thuoc_tinh_id từ $lienKetBienThe
            $giaTriThuocTinhIds = $lienKetBienThe->pluck('gia_tri_thuoc_tinh_id')->toArray(); // Trích xuất gia_tri_thuoc_tinh_id thành mảng

            $bienThe = DB::table('bien_the_san_phams as b1')
                ->join('lien_ket_bien_the_va_gia_tri_thuoc_tinhs as b2', 'b1.id', '=', 'b2.bien_the_san_pham_id')
                ->select('b1.id as bien_the_san_pham_id', 'b1.san_pham_id', 'b2.gia_tri_thuoc_tinh_id', 'b2.id as lien_ket_bien_the_va_gia_tri_thuoc_tinh_id')
                ->where('b1.san_pham_id', $data->san_pham_id)
                ->whereIn('b2.gia_tri_thuoc_tinh_id', $giaTriThuocTinhIds)
                ->get();

            // Mảng chứa hình ảnh sản phẩm
            $hinh_anh_san_pham_array = [];

            foreach ($bienThe as $lienKet) {
                // Lấy danh sách hình ảnh từ liên kết
                $hinh_anh_san_pham = HinhAnhSanPham::where('lien_ket_bien_the_va_gia_tri_thuoc_tinh_id', $lienKet->lien_ket_bien_the_va_gia_tri_thuoc_tinh_id)->get();

                if ($request->hasFile('image')) {
                    // Lặp qua từng hình ảnh để cập nhật nếu có
                    foreach ($hinh_anh_san_pham as $image) {
                        // Xóa hình ảnh cũ nếu có
                        if ($image->duong_dan_hinh_anh && Storage::exists('public/' . $image->duong_dan_hinh_anh)) {
                            Storage::delete('public/' . $image->duong_dan_hinh_anh);
                        }

                        // Lưu hình ảnh mới và cập nhật đường dẫn
                        $path = $request->file('image')->store('hinh_anh_san_phams', 'public');
                        Log::info('Đường dẫn hình ảnh mới:', ['path' => $path]);

                        // Cập nhật đường dẫn hình ảnh cho từng hình ảnh
                        $image->update([
                            'duong_dan_hinh_anh' => $path,
                        ]);

                        // Thêm vào mảng hình ảnh
                        $hinh_anh_san_pham_array[] = $image; // Thêm hình ảnh vào mảng
                    }
                } else {
                    // Nếu không có ảnh mới, thêm tất cả hình ảnh cũ vào mảng
                    $hinh_anh_san_pham_array = array_merge($hinh_anh_san_pham_array, $hinh_anh_san_pham->toArray());
                }
            }

            return response()->json([
                'message' => 'Cập nhật biến thể sản phẩm id = ' . $id,
                'data' => $data,
                'lien_ket' => $lienKetBienThe,
                'hinh_anh_san_pham' => $hinh_anh_san_pham_array,
                'bienThe' => $bienThe,
                'gia_tri' => $giaTriThuocTinhIds
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
            // Tìm biến thể sản phẩm cần xóa
            $bienTheSanPham = BienTheSanPham::findOrFail($id);

            // Kiểm tra nếu biến thể này đã được sử dụng trong chi tiết đơn hàng
            $chiTietDonHang = ChiTietDonHang::where('bien_the_san_pham_id', $id)->exists();
            if ($chiTietDonHang) {
                return response()->json([
                    'message' => 'Không thể xóa vì biến thể sản phẩm đã được sử dụng trong đơn hàng.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Lấy danh sách liên kết biến thể sản phẩm
            $lienKetBienTheVaGiaTri = $bienTheSanPham->lienKetBienTheVaGiaTri;

            // Nếu có liên kết biến thể sản phẩm, xóa các hình ảnh liên quan
            if ($lienKetBienTheVaGiaTri) {
                if ($lienKetBienTheVaGiaTri instanceof \Illuminate\Database\Eloquent\Collection) {
                    foreach ($lienKetBienTheVaGiaTri as $item) {
                        $item->hinhAnhSanPhams()->delete();
                    }
                } else {
                    $lienKetBienTheVaGiaTri->hinhAnhSanPhams()->delete();
                }
            }

            // Xóa các bản ghi liên quan trong bảng lienKetBienTheVaGiaTri
            $bienTheSanPham->lienKetBienTheVaGiaTri()->delete();

            // Xóa biến thể sản phẩm chính
            $bienTheSanPham->delete();

            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Ghi log chi tiết lỗi
            Log::error('Lỗi xóa biến thể sản phẩm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa biến thể sản phẩm: ' . $e->getMessage(),
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
