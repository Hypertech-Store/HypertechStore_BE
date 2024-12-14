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
use App\Models\HinhAnhSanPham;
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
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 10
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 9);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        $minPrice = SanPham::min('gia');
        $maxPrice = SanPham::max('gia');

        // Kiểm tra trạng thái "new" và "sale" cho mỗi sản phẩm
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
        }

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $sanPhams,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
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
            'luot_xem' => 'integer|min:0',
            'thong_so' => 'required|array',
            'thong_so.*.id' => 'required|exists:thong_sos,id',
        ]);

        // Kiểm tra thông số có thuộc danh mục không
        if ($request->has('thong_so')) {
            foreach ($validated['thong_so'] as $thongSo) {
                $thongSoModel = ThongSo::find($thongSo['id']);
                if ($thongSoModel->danh_muc_id != $validated['danh_muc_id']) {
                    return response()->json(['error' => 'Thông số không thuộc danh mục này.'], 400);
                }
            }
        }

        // Lưu thông tin sản phẩm vào database
        if ($request->hasFile('image')) {
            // Upload hình ảnh và lưu đường dẫn
            $path = $request->file('image')->store('san_phams', 'public');
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);

            // Tạo sản phẩm với đường dẫn hình ảnh đã lưu
            $sanPham = SanPham::create([
                'danh_muc_id' => $validated['danh_muc_id'],
                'danh_muc_con_id' => $validated['danh_muc_con_id'] ?? null,
                'ten_san_pham' => $validated['ten_san_pham'],
                'mo_ta' => $validated['mo_ta'] ?? null,
                'gia' => $validated['gia'],
                'so_luong_ton_kho' => $validated['so_luong_ton_kho'],
                'duong_dan_anh' => $path, // Đảm bảo bạn lưu đường dẫn ảnh
                'luot_xem' => $validated['luot_xem'] ?? 0,
            ]);
        } else {
            // Trường hợp không có ảnh thì vẫn tạo sản phẩm
            $sanPham = SanPham::create([
                'danh_muc_id' => $validated['danh_muc_id'],
                'danh_muc_con_id' => $validated['danh_muc_con_id'] ?? null,
                'ten_san_pham' => $validated['ten_san_pham'],
                'mo_ta' => $validated['mo_ta'] ?? null,
                'gia' => $validated['gia'],
                'so_luong_ton_kho' => $validated['so_luong_ton_kho'],
                'luot_xem' => $validated['luot_xem'] ?? 0,
            ]);
        }

        // Thêm thông số cho sản phẩm nếu có
        if ($request->has('thong_so') && isset($sanPham)) {
            foreach ($validated['thong_so'] as $thongSo) {
                $sanPham->thongSos()->attach($thongSo['id']);  // Liên kết thông số vào sản phẩm
            }
        }

        // Trả về phản hồi
        return response()->json(['san_pham' => $sanPham], 201);
    }

    public function getDetail($id)
    {
        // Get the product details along with its related images
        $sanPham = SanPham::with('thongSos')->find($id);

        // Check if the product exists
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $bienTheSanPhams = $sanPham->bienTheSanPhams()->with('giaTriThuocTinh')->get();

        // Tạo mảng để lưu các thuộc tính chung theo nhóm
        $groupedAttributes = [];

        foreach ($bienTheSanPhams as $bienThe) {
            // Duyệt qua các giá trị thuộc tính của biến thể sản phẩm
            foreach ($bienThe->giaTriThuocTinh as $giaTri) {
                // Nếu thuộc tính chưa có trong nhóm, tạo mới mảng với tên thuộc tính làm khóa
                if (!isset($groupedAttributes[$giaTri->thuocTinhSanPham->ten_thuoc_tinh])) {
                    $groupedAttributes[$giaTri->thuocTinhSanPham->ten_thuoc_tinh] = [
                        'ten_gia_tri' => [], // Mảng để chứa các giá trị
                        'gia_tri_thuoc_tinh_id' => [] // Mảng để chứa các gia_tri_thuoc_tinh_id
                    ];
                }

                // Thêm giá trị vào mảng
                $groupedAttributes[$giaTri->thuocTinhSanPham->ten_thuoc_tinh]['ten_gia_tri'][] = $giaTri->ten_gia_tri;
                $groupedAttributes[$giaTri->thuocTinhSanPham->ten_thuoc_tinh]['gia_tri_thuoc_tinh_id'][] = $giaTri->id; // Lưu gia_tri_thuoc_tinh_id
            }
        }

        // Lấy danh mục con và danh mục cha
        $danhMucCon = DanhMucCon::with('danhMuc')->where('id', $sanPham->danh_muc_con_id)->first();

        // Kiểm tra nếu danh mục con tồn tại và lấy tên danh mục con cùng tên danh mục cha
        $tenDanhMucCon = $danhMucCon ? $danhMucCon->ten_danh_muc_con : null;
        $tenDanhMuc = $danhMucCon && $danhMucCon->danhMuc ? $danhMucCon->danhMuc->ten_danh_muc : null;

        // Kiểm tra xem sản phẩm có đang sale không và tính phần trăm giảm giá
        $salePercentage = null;
        $saleStatus = null;

        // Lấy thông tin sale của sản phẩm
        $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');
        $sale = SaleSanPham::where('san_pham_id', $id)
            ->where('ngay_bat_dau_sale', '<=', $currentDate)
            ->where('ngay_ket_thuc_sale', '>=', $currentDate)
            ->first();

        // Kiểm tra nếu có sale và tính phần trăm giảm giá
        if ($sale) {
            $salePercentage = $sale->sale_theo_phan_tram;
            // Kiểm tra nếu sản phẩm đang sale và mới
            $saleStatus = $sale->created_at >= now()->subWeek() ? 'Both' : 'Sale';
        }

        // Kiểm tra xem sản phẩm có phải là sản phẩm mới (thêm trong 1 tuần qua)
        $isNew = null;
        if ($sanPham->created_at >= now()->subWeek()) {
            $isNew = true;
        }

        // Nếu sản phẩm vừa sale vừa mới
        if ($isNew && $salePercentage) {
            $saleStatus = 'Both';  // Sản phẩm vừa sale vừa mới
        } elseif (!$salePercentage && $isNew) {
            $saleStatus = 'New';   // Sản phẩm mới nhưng không sale
        } elseif ($salePercentage && !$isNew) {
            $saleStatus = 'Sale';  // Sản phẩm đang sale nhưng không mới
        }

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

        // Return product details along with grouped attributes, sale status, and images
        return response()->json([
            'sanPham' => $sanPham,
            'ten_danh_muc' => $tenDanhMuc,
            'ten_danh_muc_con' => $tenDanhMucCon,
            'bienTheSanPhams' => $bienTheSanPhams,
            'grouped_attributes' => $groupedAttributes,
            'sale_theo_phan_tram' => $salePercentage,
            'sale' => $sale,
            'trang_thai' => $saleStatus,
            'hinh_anh_bien_the_san_pham' => $hinhAnhBienTheSanPham, // Add images to the response
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
