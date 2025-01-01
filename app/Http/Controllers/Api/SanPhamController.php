<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreThongSoDienThoaiRequest;
use App\Http\Requests\Api\StoreThongSoDongHoRequest;
use App\Http\Requests\Api\StoreThongSoMayTinhRequest;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\DanhMuc;
use App\Models\DanhMucCon; // Import model DanhMucCon
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
    public function getAllProduct(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 9
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 9);

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
            'thuoc_tinh' => 'required|array',
            'thuoc_tinh.*.id' => 'required|exists:thuoc_tinh_san_phams,id',
            'thuoc_tinh.*.gia_tri' => 'required|array',
            'thuoc_tinh.*.gia_tri.*' => 'required|exists:gia_tri_thuoc_tinhs,id',
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
                'gia' => $giaBienThe,
                'so_luong_kho' => $validated['so_luong_ton_kho'], // Cần thay đổi nếu mỗi biến thể có số lượng riêng
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



    // Cập nhật sản phẩm
    // public function updateProduct(Request $request, $id)
    // {
    //     $sanPham = SanPham::find($id);
    //     if (!$sanPham) {
    //         return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
    //     }

    //     $request->validate([
    //         'danh_muc_id' => 'required|exists:danh_mucs,id',
    //         'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
    //         'ten_san_pham' => 'required|string|max:255',
    //         'mo_ta' => 'nullable|string',
    //         'gia' => 'required|numeric|min:0',
    //         'so_luong_ton_kho' => 'required|integer|min:0',
    //         'duong_dan_anh' => 'nullable|string',
    //         'luot_xem' => 'integer|min:0',
    //     ]);

    //     $sanPham->update($request->all());
    //     return response()->json($sanPham, 200);
    // }

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
        // Tính tổng số lượng bán của mỗi sản phẩm
        $bestSellingProduct = SanPham::join('chi_tiet_don_hangs', 'san_phams.id', '=', 'chi_tiet_don_hangs.san_pham_id')
            ->select('san_phams.id', 'san_phams.ten_san_pham', DB::raw('SUM(chi_tiet_don_hangs.so_luong) as total_quantity_sold'))
            ->groupBy('san_phams.id', 'san_phams.ten_san_pham')
            ->orderByDesc('total_quantity_sold')
            ->take(10) // Lấy 10 sản phẩm bán chạy nhất
            ->get();
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
        ]);

        // Lưu danh mục cũ để kiểm tra
        $oldDanhMucId = $sanPham->danh_muc_id;

        // Nếu có ảnh mới, lưu ảnh và cập nhật đường dẫn
        if ($request->hasFile('image')) {
            if ($sanPham->duong_dan_anh && Storage::exists('public/' . $sanPham->duong_dan_anh)) {
                Storage::delete('public/' . $sanPham->duong_dan_anh);
            }

            $path = $request->file('image')->store('san_phams', 'public');
            $validated['duong_dan_anh'] = $path;
        } else {
            // Nếu không có ảnh mới, giữ nguyên ảnh cũ
            $validated['duong_dan_anh'] = $sanPham->duong_dan_anh;
        }

        // Kết hợp dữ liệu cũ và dữ liệu mới
        $updatedData = array_merge($sanPham->toArray(), $validated);

        // Cập nhật dữ liệu
        $sanPham->update($updatedData);

        return response()->json($sanPham, 201);
    }

    public function deleteOldTechnicalSpecs($oldDanhMucId, $sanPhamId)
    {
        switch ($oldDanhMucId) {
            case 1: // Máy tính
                ThongSoMayTinh::where('san_pham_id', $sanPhamId)->delete();
                break;
            case 2: // Điện thoại
                ThongSoDienThoai::where('san_pham_id', $sanPhamId)->delete();
                break;
            case 3: // Đồng hồ
                ThongSoDongHo::where('san_pham_id', $sanPhamId)->delete();
                break;
        }
    }
}
