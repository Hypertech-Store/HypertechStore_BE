<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\BienTheSanPham;
use App\Models\DanhMuc;
use App\Models\DanhMucCon; // Import model DanhMucCon
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SanPhamController extends Controller
{
    public function getAllProduct(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 10
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        $minPrice = SanPham::min('gia');
        $maxPrice = SanPham::max('gia');

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
        $request->validate([
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'duong_dan_anh' => 'nullable|string',
            'luot_xem' => 'integer|min:0',
        ]);

        $sanPham = SanPham::create($request->all());
        return response()->json($sanPham, 201);
    }

    public function getDetail($id)
    {
        // Get the product details along with its related images
        $sanPham = SanPham::with('hinhAnhSanPhams')->find($id);

        // Check if the product exists
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Fetch related variants (BienTheSanPham) by san_pham_id
        $bienTheSanPhams = BienTheSanPham::where('san_pham_id', $id)->get(['ten_bien_the', 'gia_tri_bien_the']);

        // Lấy danh mục con và danh mục cha
        $danhMucCon = DanhMucCon::with('danhMuc')->where('id', $sanPham->danh_muc_con_id)->first(); // Giả sử `sanPham` có trường `danh_muc_con_id`

        // Kiểm tra nếu danh mục con tồn tại và lấy tên danh mục con cùng tên danh mục cha
        $tenDanhMucCon = $danhMucCon ? $danhMucCon->ten_danh_muc_con : null;
        $tenDanhMuc = $danhMucCon && $danhMucCon->danhMuc ? $danhMucCon->danhMuc->ten_danh_muc : null;

        // Return the product details along with variants
        return response()->json([
            'sanPham' => $sanPham,
            'bienTheSanPhams' => $bienTheSanPhams,
            'ten_danh_muc_con' => $tenDanhMucCon,
            'ten_danh_muc' => $tenDanhMuc
        ], 200);
    }


    // Cập nhật sản phẩm
    public function updateProduct(Request $request, $id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $request->validate([
            'danh_muc_id' => 'required|exists:danh_mucs,id',
            'danh_muc_con_id' => 'nullable|exists:danh_muc_cons,id',
            'ten_san_pham' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'duong_dan_anh' => 'nullable|string',
            'luot_xem' => 'integer|min:0',
        ]);

        $sanPham->update($request->all());
        return response()->json($sanPham, 200);
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


}
