<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreThongSoDienThoaiRequest;
use App\Http\Requests\Api\StoreThongSoDongHoRequest;
use App\Http\Requests\Api\StoreThongSoMayTinhRequest;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\DanhGia;
use App\Models\DanhMuc;
use App\Models\DanhMucCon; // Import model DanhMucCon
use App\Models\DonHang;
use App\Models\GiaTriThuocTinh;
use App\Models\HinhAnhSanPham;
use App\Models\LienKetBienTheVaGiaTriThuocTinh;
use App\Models\SaleSanPham;
use App\Models\ThongSo;
use App\Models\ThongSoDienThoai;
use App\Models\ThongSoDongHo;
use App\Models\ThongSoMayTinh;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SanPhamController extends Controller
{
    public function getAllProductAdmin(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 9
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        $minPrice = SanPham::min('gia');
        $maxPrice = SanPham::max('gia');

        // Kiểm tra trạng thái "new" và "sale" và lấy hình ảnh sản phẩm
        foreach ($sanPhams as $sanPham) {
            $saleStatus = null;
            $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');

            // Kiểm tra trạng thái sale của sản phẩm
            $sale = SaleSanPham::where('san_pham_id', $sanPham->id)
                ->where('ngay_bat_dau_sale', '<=', $currentDate)
                ->where('ngay_ket_thuc_sale', '>=', $currentDate)
                ->first();

            if ($sale) {
                $saleStatus = $sale->sale_theo_phan_tram;

                // Nếu sản phẩm có sale và được tạo trong vòng 1 tuần
                $saleStatus = $sale->created_at >= now()->subWeek() ? 'Sản phẩm mới đang sale' : 'Sale';
            }

            // Kiểm tra xem sản phẩm có phải là sản phẩm mới (thêm trong 1 tuần qua)
            $isNew = null;
            if ($sanPham->created_at >= now()->subWeek()) {
                $isNew = true;
            }

            // Xử lý trạng thái "new" và "sale" cho sản phẩm
            $status = null;
            if ($saleStatus && $isNew) {
                $status = 'Sản phẩm mới đang sale';  // Cả sale và new
            } elseif ($saleStatus) {
                $status = 'Sale';  // Chỉ sale
            } elseif ($isNew) {
                $status = 'Sản phẩm mới';   // Chỉ mới
            }

            // Thêm trạng thái vào mỗi sản phẩm
            $sanPham->trang_thai = $status;

            // Lấy biến thể sản phẩm và hình ảnh
            $bienTheSanPhams = $sanPham->bienTheSanPhams()->with('giaTriThuocTinh')->get();
            $hinhAnhBienTheSanPham = [];
            foreach ($bienTheSanPhams as $bienThe) {
                $variantImages = [];
                foreach ($bienThe->lienKetBienTheVaGiaTri as $lienKet) {
                    foreach ($lienKet->hinhAnhSanPhams as $hinhAnh) {
                        $variantImages[] = [
                            'bien_the_id' => $bienThe->id,
                            'gia_tri_thuoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                            'ten_gia_tri' => $lienKet->giaTriThuocTinh->ten_gia_tri, // Thêm tên giá trị thuộc tính
                            'hinh_anh_id' => $hinhAnh->id,
                            'duong_dan_hinh_anh' => $hinhAnh->duong_dan_hinh_anh
                        ];
                    }
                }
                $hinhAnhBienTheSanPham[] = [
                    'bien_the_id' => $bienThe->id,
                    'hinh_anh' => $variantImages
                ];
            }

            // Thêm hình ảnh biến thể vào sản phẩm
            $sanPham->hinh_anh_san_pham = $hinhAnhBienTheSanPham;
        }

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhams,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ]);
    }

    public function getAllProductClient(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 9
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', default: 9);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        $minPrice = SanPham::min('gia');
        $maxPrice = SanPham::max('gia');

        // Kiểm tra trạng thái "new" và "sale" và lấy hình ảnh sản phẩm
        foreach ($sanPhams as $sanPham) {
            $saleStatus = null;
            $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');

            // Kiểm tra trạng thái sale của sản phẩm
            $sale = SaleSanPham::where('san_pham_id', $sanPham->id)
                ->where('ngay_bat_dau_sale', '<=', $currentDate)
                ->where('ngay_ket_thuc_sale', '>=', $currentDate)
                ->first();

            if ($sale) {
                $saleStatus = $sale->sale_theo_phan_tram;

                // Nếu sản phẩm có sale và được tạo trong vòng 1 tuần
                $saleStatus = $sale->created_at >= now()->subWeek() ? 'Sản phẩm mới đang sale' : 'Sale';
            }

            // Kiểm tra xem sản phẩm có phải là sản phẩm mới (thêm trong 1 tuần qua)
            $isNew = null;
            if ($sanPham->created_at >= now()->subWeek()) {
                $isNew = true;
            }

            // Xử lý trạng thái "new" và "sale" cho sản phẩm
            $status = null;
            if ($saleStatus && $isNew) {
                $status = 'Sản phẩm mới đang sale';  // Cả sale và new
            } elseif ($saleStatus) {
                $status = 'Sale';  // Chỉ sale
            } elseif ($isNew) {
                $status = 'Sản phẩm mới';   // Chỉ mới
            }

            // Thêm trạng thái vào mỗi sản phẩm
            $sanPham->trang_thai = $status;

            // Lấy biến thể sản phẩm và hình ảnh
            $bienTheSanPhams = $sanPham->bienTheSanPhams()->with('giaTriThuocTinh')->get();
            $hinhAnhBienTheSanPham = [];
            foreach ($bienTheSanPhams as $bienThe) {
                $variantImages = [];
                foreach ($bienThe->lienKetBienTheVaGiaTri as $lienKet) {
                    foreach ($lienKet->hinhAnhSanPhams as $hinhAnh) {
                        $variantImages[] = [
                            'bien_the_id' => $bienThe->id,
                            'gia_tri_thuoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                            'ten_gia_tri' => $lienKet->giaTriThuocTinh->ten_gia_tri, // Thêm tên giá trị thuộc tính
                            'hinh_anh_id' => $hinhAnh->id,
                            'duong_dan_hinh_anh' => $hinhAnh->duong_dan_hinh_anh
                        ];
                    }
                }
                $hinhAnhBienTheSanPham[] = [
                    'bien_the_id' => $bienThe->id,
                    'hinh_anh' => $variantImages
                ];
            }

            // Thêm hình ảnh biến thể vào sản phẩm
            $sanPham->hinh_anh_san_pham = $hinhAnhBienTheSanPham;
        }

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhams,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ]);
    }

    public function searchProduct(Request $request): JsonResponse
    {
        // Lấy từ khóa tìm kiếm từ query param, giá trị mặc định là rỗng
        $searchTerm = $request->query('keyword', '');

        // Kiểm tra nếu không có từ khóa tìm kiếm
        if (empty($searchTerm)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng nhập từ khóa tìm kiếm.'
            ], 400);
        }

        // Lấy số trang và số sản phẩm mỗi trang từ query param, mặc định: page = 1, number_row = 9
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 9);

        // Tìm kiếm sản phẩm dựa trên 'ten_san_pham' hoặc 'mo_ta'
        $query = SanPham::query();
        $query->where('ten_san_pham', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('mo_ta', 'LIKE', '%' . $searchTerm . '%');

        // Phân trang kết quả
        $sanPhams = $query->paginate($numberRow, ['*'], 'page', $page);

        // Kiểm tra xem có sản phẩm nào không
        if ($sanPhams->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có dữ liệu nào về từ khóa "' . $searchTerm . '"'
            ], 404);
        }

        // Lấy giá trị min và max giá của sản phẩm
        $minPrice = $query->min('gia');
        $maxPrice = $query->max('gia');

        // Kiểm tra trạng thái "new" và "sale"
        $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');
        foreach ($sanPhams as $sanPham) {
            $sale = SaleSanPham::where('san_pham_id', $sanPham->id)
                ->where('ngay_bat_dau_sale', '<=', $currentDate)
                ->where('ngay_ket_thuc_sale', '>=', $currentDate)
                ->first();

            $saleStatus = $sale ? 'Sale' : null;
            $isNew = $sanPham->created_at >= now()->subWeek();
            $status = $saleStatus && $isNew ? 'Sản phẩm mới đang sale' : ($saleStatus ?: ($isNew ? 'Sản phẩm mới' : null));
            $sanPham->trang_thai = $status;

            // Xử lý hình ảnh biến thể
            $bienTheSanPhams = $sanPham->bienTheSanPhams()->with('giaTriThuocTinh')->get();
            $hinhAnhBienTheSanPham = [];
            foreach ($bienTheSanPhams as $bienThe) {
                $variantImages = [];
                foreach ($bienThe->lienKetBienTheVaGiaTri as $lienKet) {
                    foreach ($lienKet->hinhAnhSanPhams as $hinhAnh) {
                        $variantImages[] = [
                            'bien_the_id' => $bienThe->id,
                            'gia_tri_thoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                            'ten_gia_tri' => $lienKet->giaTriThuocTinh->ten_gia_tri,
                            'hinh_anh_id' => $hinhAnh->id,
                            'duong_dan_hinh_anh' => $hinhAnh->duong_dan_hinh_anh
                        ];
                    }
                }
                $hinhAnhBienTheSanPham[] = [
                    'bien_the_id' => $bienThe->id,
                    'hinh_anh' => $variantImages
                ];
            }
            $sanPham->hinh_anh_san_pham = $hinhAnhBienTheSanPham;
        }

        // Trả về kết quả JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhams,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ]);
    }



    public function getAllSanPham(): JsonResponse
    {
        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::query()->get();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhams,
        ]);
    }

    public function getSanPhamChuaSale(): JsonResponse
    {
        // Lấy các sản phẩm chưa sale (không có liên kết với saleSanPhams)
        $sanPhamsChuaSale = SanPham::doesntHave('saleSanPhams')->get();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhamsChuaSale,
        ]);
    }



    public function createProduct(Request $request)
    {
        // Xác thực dữ liệu từ request
        $validated = $request->validate([
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'luot_xem' => 'integer|min:0',
            'thuoc_tinh' => 'nullable|array',
            'thuoc_tinh.*.id' => 'nullable|exists:thuoc_tinh_san_phams,id',
            'thuoc_tinh.*.gia_tri' => 'nullable|array',
            'thuoc_tinh.*.gia_tri.*' => 'nullable|exists:gia_tri_thuoc_tinhs,id',
            'gia_bien_the' => 'nullable|array',
            'gia_bien_the.*' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('san_phams', 'public');
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);
        }

        // Tạo sản phẩm chính
        $sanPham = SanPham::create([
            'danh_muc_id' => $validated['danh_muc_id'],
            'danh_muc_con_id' => $validated['danh_muc_con_id'] ?? null,
            'ten_san_pham' => $validated['ten_san_pham'],
            'mo_ta' => $validated['mo_ta'] ?? null,
            'gia' => $validated['gia'],
            'so_luong_ton_kho' => $validated['so_luong_ton_kho'],
            'duong_dan_anh' => $path,
            'luot_xem' => $validated['luot_xem'] ?? 0,
        ]);


        // Lưu thuộc tính và tự động sinh biến thể
        $thuocTinhValues = [];

        foreach ($validated['thuoc_tinh'] as $thuocTinh) {
            $giaTriIds = $thuocTinh['gia_tri'];
            $thuocTinhValues[] = $giaTriIds;
        }

        // Sinh tất cả các kết hợp của giá trị thuộc tính
        $combinations = $this->generateCombinations($thuocTinhValues);

        foreach ($combinations as $index => $combination) {
            // Sử dụng giá biến thể từ request nếu có, mặc định là giá sản phẩm chính
            $giaBienThe = $validated['gia_bien_the'][$index] ?? $validated['gia'];

            // Tạo biến thể sản phẩm
            $bienTheSanPham = BienTheSanPham::create([
                'san_pham_id' => $sanPham->id,
                'gia' => 0,
                'so_luong_kho' => 0, // Cần thay đổi nếu mỗi biến thể có số lượng riêng
            ]);

            // Liên kết các giá trị thuộc tính với biến thể
            foreach ($combination as $giaTriId) {
                $giaTriId = (int) $giaTriId;
                $lien_ket = LienKetBienTheVaGiaTriThuocTinh::create([
                    'bien_the_san_pham_id' => $bienTheSanPham->id,
                    'gia_tri_thuoc_tinh_id' => $giaTriId,
                ]);

                // Kiểm tra nếu gia_tri_thuoc_tinh_id là thuộc tính màu sắc (thuoc_tinh_id = 1)
                $giaTri = GiaTriThuocTinh::find($giaTriId); // Lấy giá trị thuộc tính
                if ($giaTri && $giaTri->thuoc_tinh_san_pham_id == 1) { // Kiểm tra thuộc tính màu sắc
                    // Tạo HinhAnhSanPham với duong_dan_hinh_anh = null
                    HinhAnhSanPham::create([
                        'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => $lien_ket->id,
                        'duong_dan_hinh_anh' => null,
                    ]);
                }
            }
        }

        // Trả về phản hồi
        return response()->json([
            'san_pham' => $sanPham,
            'so_bien_the' => count($combinations),
        ], 201);
    }

    /**
     * Hàm sinh tất cả các tổ hợp từ danh sách giá trị thuộc tính
     */
    private function generateCombinations($arrays)
    {
        $result = [[]];

        foreach ($arrays as $propertyValues) {
            $tmp = [];
            foreach ($result as $resultItem) {
                foreach ($propertyValues as $propertyValue) {
                    $tmp[] = array_merge($resultItem, [$propertyValue]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }

    public function updateProduct(Request $request, $id)
    {
        $sanPham = SanPham::find($id);

        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $validated = $request->validate([
            'danh_muc_id' => 'nullable|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'nullable|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'nullable|numeric|min:0',
            'so_luong_ton_kho' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'luot_xem' => 'nullable|integer|min:0',
            'thuoc_tinh' => 'nullable|array',
            'thuoc_tinh.*.id' => 'nullable|exists:thuoc_tinh_san_phams,id',
            'thuoc_tinh.*.gia_tri' => 'nullable|array',
            'thuoc_tinh.*.gia_tri.*' => 'nullable|exists:gia_tri_thuoc_tinhs,id',
            'gia_bien_the' => 'nullable|array',
            'gia_bien_the.*' => 'nullable|numeric|min:0',
        ]);

        // Xử lý ảnh mới
        if ($request->hasFile('image')) {
            if ($sanPham->duong_dan_anh && Storage::exists('public/' . $sanPham->duong_dan_anh)) {
                Storage::delete('public/' . $sanPham->duong_dan_anh);
            }
            $path = $request->file('image')->store('san_phams', 'public');
            $validated['duong_dan_anh'] = $path;
        }

        $sanPham->update($validated);

        // Cập nhật thuộc tính và biến thể
        if ($request->has('thuoc_tinh')) {
            $thuocTinhValues = [];
            foreach ($validated['thuoc_tinh'] as $thuocTinh) {
                $giaTriIds = $thuocTinh['gia_tri'] ?? [];
                $thuocTinhValues[] = $giaTriIds;
            }

            $existingCombinations = $sanPham->bienTheSanPhams()
                ->with('lienKetBienTheVaGiaTri')
                ->get()
                ->map(function ($bienThe) {
                    return $bienThe->lienKetBienTheVaGiaTri->pluck('gia_tri_thuoc_tinh_id')->sort()->values()->toArray();
                })
                ->toArray();

            $newCombinations = $this->generateCombinations($thuocTinhValues);

            foreach ($newCombinations as $index => $combination) {
                $sortedCombination = collect($combination)->sort()->values()->toArray();
                if (in_array($sortedCombination, $existingCombinations)) {
                    continue;
                }

                $giaBienThe = $validated['gia_bien_the'][$index] ?? $sanPham->gia;
                $bienTheSanPham = BienTheSanPham::create([
                    'san_pham_id' => $sanPham->id,
                    'gia' => $giaBienThe,
                    'so_luong_kho' => 0,
                ]);

                foreach ($sortedCombination as $giaTriId) {
                    $giaTriId = (int) $giaTriId;
                    $lien_ket = LienKetBienTheVaGiaTriThuocTinh::create([
                        'bien_the_san_pham_id' => $bienTheSanPham->id,
                        'gia_tri_thuoc_tinh_id' => $giaTriId,
                    ]);

                    $giaTri = GiaTriThuocTinh::find($giaTriId);
                    if ($giaTri && $giaTri->thuoc_tinh_san_pham_id == 1) {
                        HinhAnhSanPham::create([
                            'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id' => $lien_ket->id,
                            'duong_dan_hinh_anh' => null,
                        ]);
                    }
                }
            }

            foreach ($existingCombinations as $existingCombination) {
                if (!in_array($existingCombination, $newCombinations)) {
                    $bienThe = $sanPham->bienTheSanPhams()
                        ->whereHas('lienKetBienTheVaGiaTri', function ($query) use ($existingCombination) {
                            $query->whereIn('gia_tri_thuoc_tinh_id', $existingCombination);
                        })
                        ->first();

                    if ($bienThe) {
                        // Xóa hình ảnh liên quan
                        HinhAnhSanPham::whereIn(
                            'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id',
                            $bienThe->lienKetBienTheVaGiaTri->pluck('id')
                        )->delete();

                        // Xóa liên kết thuộc tính
                        $bienThe->lienKetBienTheVaGiaTri()->delete();

                        // Xóa biến thể
                        $bienThe->delete();
                    }
                }
            }
        }

        return response()->json([
            'data' => $sanPham,
            'newCombinations' => $newCombinations,
            'existingCombinations' => $existingCombinations

        ], 200);
    }



    public function getDetail($id)
    {
        // Get the product details along with its related images
        $sanPham = SanPham::with('sanPhamVaThongSo.thongSo')->find($id);

        // Format the product specifications
        $thongSoSanPham = $sanPham->sanPhamVaThongSo->map(function ($sanPhamVaThongSo) {
            return [
                'thong_so' => $sanPhamVaThongSo->thongSo->ten_thong_so,  // Information name
                'mo_ta' => $sanPhamVaThongSo->mo_ta, // Description
            ];
        });

        $sanPham['thong_so'] = $thongSoSanPham;
        unset($sanPham->sanPhamVaThongSo); // Remove joined data that we no longer need

        // Check if the product exists
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Retrieve all variants and their attribute values
        $bienTheSanPhams = $sanPham->bienTheSanPhams()->with('giaTriThuocTinh')->get();

        // Initialize the grouped attributes array
        $groupedAttributes = [];

        // Loop through each product variant
        foreach ($bienTheSanPhams as $bienThe) {
            foreach ($bienThe->giaTriThuocTinh as $giaTri) {
                // Initialize a new attribute group if not present
                $attributeName = $giaTri->thuocTinhSanPham->ten_thuoc_tinh;
                if (!isset($groupedAttributes[$attributeName])) {
                    $groupedAttributes[$attributeName] = [
                        'ten_gia_tri' => [],
                        'gia_tri_thuoc_tinh_id' => []
                    ];
                }

                // Check if the value hasn't been added yet
                if (!in_array($giaTri->id, $groupedAttributes[$attributeName]['gia_tri_thuoc_tinh_id'])) {
                    // Add new value to the respective group
                    $groupedAttributes[$attributeName]['ten_gia_tri'][] = $giaTri->ten_gia_tri;
                    $groupedAttributes[$attributeName]['gia_tri_thuoc_tinh_id'][] = $giaTri->id;
                }
            }
        }

        // Handle the category and sale details of the product
        $danhMucCon = DanhMucCon::with('danhMuc')->where('id', $sanPham->danh_muc_con_id)->first();
        $tenDanhMucCon = $danhMucCon ? $danhMucCon->ten_danh_muc_con : null;
        $tenDanhMuc = $danhMucCon && $danhMucCon->danhMuc ? $danhMucCon->danhMuc->ten_danh_muc : null;

        // Retrieve sale information and calculate the sale status
        $salePercentage = null;
        $saleStatus = null;

        // Sale check logic
        $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');
        $sale = SaleSanPham::where('san_pham_id', $id)
            ->where('ngay_bat_dau_sale', '<=', $currentDate)
            ->where('ngay_ket_thuc_sale', '>=', $currentDate)
            ->first();

        // Sale percentage calculation and status update
        if ($sale) {
            $salePercentage = $sale->sale_theo_phan_tram;
            $saleStatus = $sale->created_at >= now()->subWeek() ? 'Both' : 'Sale';
        }

        $isNew = $sanPham->created_at >= now()->subWeek() ? true : null;

        // Define sale status for new and sale items
        if ($isNew && $salePercentage) {
            $saleStatus = 'Both';
        } elseif (!$salePercentage && $isNew) {
            $saleStatus = 'New';
        } elseif ($salePercentage && !$isNew) {
            $saleStatus = 'Sale';
        }

        // Prepare variant images with uniqueness check
        $hinhAnhBienTheSanPham = [];
        $seenGiaTriThuocTinh = [];

        foreach ($bienTheSanPhams as $bienThe) {
            $variantImages = [];

            foreach ($bienThe->lienKetBienTheVaGiaTri as $lienKet) {
                foreach ($lienKet->hinhAnhSanPhams as $hinhAnh) {
                    if (!in_array($lienKet->gia_tri_thuoc_tinh_id, $seenGiaTriThuocTinh)) {
                        $variantImages[] = [
                            'bien_the_id' => $bienThe->id,
                            'gia_tri_thuoc_tinh_id' => $lienKet->gia_tri_thuoc_tinh_id,
                            'ten_gia_tri' => $lienKet->giaTriThuocTinh->ten_gia_tri,
                            'hinh_anh_id' => $hinhAnh->id,
                            'duong_dan_hinh_anh' => $hinhAnh->duong_dan_hinh_anh
                        ];
                        $seenGiaTriThuocTinh[] = $lienKet->gia_tri_thuoc_tinh_id; // Mark the value as seen
                    }
                }
            }

            if (!empty($variantImages)) {
                $hinhAnhBienTheSanPham[] = [
                    'bien_the_id' => $bienThe->id,
                    'hinh_anh' => $variantImages,
                ];
            }
        }

        // Return final response with product details, grouped attributes, and sale status
        return response()->json([
            'sanPham' => $sanPham,
            'ten_danh_muc' => $tenDanhMuc,
            'ten_danh_muc_con' => $tenDanhMucCon,
            'bienTheSanPhams' => $bienTheSanPhams,
            'grouped_attributes' => $groupedAttributes,
            'sale_theo_phan_tram' => $salePercentage,
            'sale' => $sale,
            'trang_thai' => $saleStatus,
            'hinh_anh_bien_the_san_pham' => $hinhAnhBienTheSanPham
        ], 200);
    }


    // Xóa sản phẩm
    public function deleteProduct($id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $sanPham->delete();
        return response()->json(['message' => 'Xóa sản phẩm thành công'], 200);
    }
    public function getNewProducts()
    {
        $sevenDaysAgo = now()->subDays(7); // Lấy thời điểm 7 ngày trước

        $data = SanPham::where('created_at', '>=', $sevenDaysAgo)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'message' => 'Lấy sản phẩm mới nhất trong 7 ngày thành công!',
            'data' => $data
        ]);
    }

    public function getBestSellingProduct()
    {
        // Lấy sản phẩm bán chạy với tổng số lượng đã bán và thông tin giảm giá từ bảng SaleSanPham
        $bestSellingProduct = SanPham::with(['danhGias' => function ($query) {
            $query->where('trang_thai', 1); // Chỉ lấy đánh giá có trạng thái = 1
        }])
            ->withCount(['chiTietDonHangs as tong_luot_mua' => function ($query) {
                $query->select(DB::raw('SUM(so_luong)')); // Tổng số lượng sản phẩm đã bán
            }])
            ->with(['saleSanPhams' => function ($query) {
                $query->select('san_pham_id', 'sale_theo_phan_tram'); // Chỉ lấy thông tin giảm giá
            }])
            ->orderByDesc('tong_luot_mua') // Sắp xếp theo tổng số lượng đã bán
            ->take(10)
            ->get();

        // Xử lý từng sản phẩm
        $bestSellingProduct->each(function ($product) {
            $totalStars = $product->danhGias->sum('danh_gia'); // Tổng số sao
            $totalReviews = $product->danhGias->count(); // Tổng số lượt đánh giá

            // Tính số lượng khách hàng duy nhất đánh giá sản phẩm (dựa trên khach_hang_id)
            $totalUniqueCustomers = $product->danhGias->pluck('khach_hang_id')->unique()->count();

            // Tính điểm trung bình sao và tổng số đánh giá
            $product->trung_binh_sao = $totalReviews > 0 ? round($totalStars / $totalReviews, 2) : 0; // Điểm trung bình sao
            $product->tong_so_danh_gia = $totalReviews; // Tổng số lượt đánh giá
            $product->tong_khach_hang_danh_gia = $totalUniqueCustomers; // Tổng số khách hàng duy nhất

            // Kiểm tra sản phẩm có sale không và lấy phần trăm giảm giá
            if ($product->saleSanPhams) {
                $product->sale_percentage = $product->saleSanPhams->sale_theo_phan_tram; // Lấy phần trăm giảm giá
            } else {
                $product->sale_percentage = 0; // Không có sale
            }

            // Xóa danhGias và saleSanPham khỏi kết quả trả về
            unset($product->danhGias);
            unset($product->saleSanPhams);
        });

        // Trả về danh sách sản phẩm
        return response()->json($bestSellingProduct);
    }




    public function getSanPhamTheoDanhMuc($danhMucId)
    {

        $danhMuc = DanhMuc::with('sanPhams')->find($danhMucId);

        if (!$danhMuc) {
            return response()->json([
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }


        return response()->json([
            'danh_muc' => $danhMuc->ten_danh_muc,
            'san_phams' => $danhMuc->sanPhams
        ], 200);
    }
    public function getSanPhamTheoDanhMucCon($danhMucConId)
    {
        // Tìm danh mục con theo id
        $danhMucCon = DanhMuc::with('sanPhams')->find($danhMucConId);

        if (!$danhMucCon) {
            return response()->json([
                'message' => 'Danh mục con không tồn tại.'
            ], 404); // Trả về lỗi 404 nếu không tìm thấy danh mục con
        }

        // Trả về các sản phẩm trong danh mục con dưới dạng JSON
        return response()->json([
            'danh_muc_con' => $danhMucCon->ten_danh_muc,
            'san_phams' => $danhMucCon->sanPhams
        ], 200); // Trả về sản phẩm dưới dạng JSON
    }

    public function timKiemSanPham(Request $request)
    {
        $query = $request->input('query'); // Lấy từ khóa tìm kiếm

        if (!$query) {
            return response()->json(['message' => 'Vui lòng nhập từ khóa tìm kiếm.'], 400);
        }

        $sanPhams = SanPham::where('ten_san_pham', 'like', "%$query%")
            ->orWhere('mo_ta', 'like', "%$query%")
            ->get();

        return response()->json($sanPhams);
    }
    public function locSanPhamTheoGia(Request $request)
    {
        // Lấy giá trị min và max từ request, mặc định là 0 cho min và 10000000 cho max
        $minPrice = $request->min;
        $maxPrice = $request->max;

        // Thực hiện query để lấy sản phẩm trong khoảng giá
        $sanPhams = SanPham::whereBetween('gia', [$minPrice, $maxPrice])->get();

        // Trả về view với danh sách sản phẩm
        return response()->json($sanPhams);
    }
    public function kiemTraSanPhamDaMua(Request $request, $sanPhamId)
    {
        $khachHangId = $request->input('khach_hang_id');

        // Kiểm tra nếu khách hàng đã mua sản phẩm với trạng thái đơn hàng thành công và không quá 3 ngày kể từ lần cập nhật
        $daMuaSanPham = DonHang::where('khach_hang_id', $khachHangId)
            ->where('trang_thai_don_hang_id', 5) // Trạng thái thành công
            ->whereHas('chiTietDonHangs', function ($query) use ($sanPhamId) {
                $query->where('san_pham_id', $sanPhamId);
            })
            ->where(function ($query) {
                // Kiểm tra nếu đơn hàng được cập nhật trong vòng 3 ngày
                $query->whereRaw('DATEDIFF(NOW(), updated_at) <= 3');
            })
            ->exists();

        // Nếu có bất kỳ điều kiện nào không thỏa mãn, trả về false
        if (!$daMuaSanPham) {
            return response()->json([
                'status' => 'success',
                'da_mua' => false,
                'message' => 'Không thể đánh giá sản phẩm vì đơn hàng không thành công hoặc đã quá 3 ngày.',
                'data' => $daMuaSanPham
            ]);
        }

        return response()->json([
            'status' => 'success',
            'da_mua' => true,
            'message' => 'Khách hàng có thể đánh giá sản phẩm.',
            'data' => $daMuaSanPham
        ]);
    }
}
