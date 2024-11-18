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
use App\Models\ThongSoDienThoai;
use App\Models\ThongSoDongHo;
use App\Models\ThongSoMayTinh;
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


    public function createProduct(Request $request, StoreThongSoMayTinhRequest $requestMayTinh, StoreThongSoDongHoRequest $requestDongHo, StoreThongSoDienThoaiRequest $requestDienThoai)
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

        // Tạo sản phẩm
        $sanPham = SanPham::create($request->all());
        $thongSoDienThoai = [];
        $thongSoMayTinh = [];
        $thongSoDongHo = [];

        // Thêm thông số theo danh mục
        switch ($request->danh_muc_id) {
            case 1: // Máy tính
                $thongSoMayTinh = ThongSoMayTinh::create([
                    'san_pham_id' => $sanPham->id,
                    'cong_nghe_cpu' => $requestMayTinh->cong_nghe_cpu,
                    'so_nhan' => $requestMayTinh->so_nhan,
                    'so_luong_luong' => $requestMayTinh->so_luong_luong,
                    'toc_do_cpu' => $requestMayTinh->toc_do_cpu,
                    'toc_do_toi_da' => $requestMayTinh->toc_do_toi_da,
                    'bo_nho_cache' => $requestMayTinh->bo_nho_cache,
                    'ram' => $requestMayTinh->ram,
                    'loai_ram' => $requestMayTinh->loai_ram,
                    'toc_do_bus_ram' => $requestMayTinh->toc_do_bus_ram,
                    'ho_tro_ram_toi_da' => $requestMayTinh->ho_tro_ram_toi_da,
                    'o_cung' => $requestMayTinh->o_cung,
                    'man_hinh' => $requestMayTinh->man_hinh,
                    'do_phan_giai' => $requestMayTinh->do_phan_giai,
                    'tan_so_quet' => $requestMayTinh->tan_so_quet,
                    'cong_nghe_man_hinh' => $requestMayTinh->cong_nghe_man_hinh,
                    'card_do_hoa' => $requestMayTinh->card_do_hoa,
                    'cong_nghe_am_thanh' => $requestMayTinh->cong_nghe_am_thanh,
                    'cong_giao_tiep' => $requestMayTinh->cong_giao_tiep,
                    'ket_noi_khong_day' => $requestMayTinh->ket_noi_khong_day,
                    'webcam' => $requestMayTinh->webcam,
                    'tinh_nang_khac' => $requestMayTinh->tinh_nang_khac,
                    'den_ban_phim' => $requestMayTinh->den_ban_phim,
                    'khoi_luong' => $requestMayTinh->khoi_luong,
                    'thoi_diem_ra_mat' => $requestMayTinh->thoi_diem_ra_mat,
                ]);
                break;
            case 2: // Điện thoại
                $thongSoDienThoai = ThongSoDienThoai::create([
                    'san_pham_id' => $sanPham->id,
                    'he_dieu_hanh' => $requestDienThoai->he_dieu_hanh,
                    'chip_xu_ly' => $requestDienThoai->chip_xu_ly,
                    'toc_do_cpu' => $requestDienThoai->toc_do_cpu,
                    'chip_do_hoa' => $requestDienThoai->chip_do_hoa,
                    'ram' => $requestDienThoai->ram,
                    'dung_luong_luu_tru' => $requestDienThoai->dung_luong_luu_tru,
                    'dung_luong_con_lai' => $requestDienThoai->dung_luong_con_lai,
                    'the_nho' => $requestDienThoai->the_nho,
                    'danh_ba' => $requestDienThoai->danh_ba,
                    'camera_sau_resolution' => $requestDienThoai->camera_sau_resolution,
                    'camera_sau_video' => $requestDienThoai->camera_sau_video,
                    'camera_sau_flash' => $requestDienThoai->camera_sau_flash,
                    'camera_sau_tinh_nang' => $requestDienThoai->camera_sau_tinh_nang,
                    'camera_truoc_resolution' => $requestDienThoai->camera_truoc_resolution,
                    'camera_truoc_tinh_nang' => $requestDienThoai->camera_truoc_tinh_nang,
                    'cong_nghe_man_hinh' => $requestDienThoai->cong_nghe_man_hinh,
                    'man_hinh_resolution' => $requestDienThoai->man_hinh_resolution,
                    'man_hinh_rong' => $requestDienThoai->man_hinh_rong,
                    'man_hinh_do_sang_max' => $requestDienThoai->man_hinh_do_sang_max,
                    'mat_kinh_cam_ung' => $requestDienThoai->mat_kinh_cam_ung,
                    'dung_luong_pin' => $requestDienThoai->dung_luong_pin,
                    'loai_pin' => $requestDienThoai->loai_pin,
                    'sac_toi_da' => $requestDienThoai->sac_toi_da,
                    'sac_kem_theo' => $requestDienThoai->sac_kem_theo,
                    'cong_nghe_pin' => $requestDienThoai->cong_nghe_pin,
                    'bao_mat_nang_cao' => $requestDienThoai->bao_mat_nang_cao,
                    'tinh_nang_dac_biet' => $requestDienThoai->tinh_nang_dac_biet,
                    'khang_nuoc_bui' => $requestDienThoai->khang_nuoc_bui,
                    'ghi_am' => $requestDienThoai->ghi_am,
                    'radio' => $requestDienThoai->radio,
                    'xem_phim' => $requestDienThoai->xem_phim,
                    'nghe_nhac' => $requestDienThoai->nghe_nhac,
                    'mang_di_dong' => $requestDienThoai->mang_di_dong,
                    'sim' => $requestDienThoai->sim,
                    'wifi' => $requestDienThoai->wifi,
                    'gps' => $requestDienThoai->gps,
                    'bluetooth' => $requestDienThoai->bluetooth,
                    'cong_ket_noi_sac' => $requestDienThoai->cong_ket_noi_sac,
                    'jack_tai_nghe' => $requestDienThoai->jack_tai_nghe,
                    'ket_noi_khac' => $requestDienThoai->ket_noi_khac,
                    'thiet_ke' => $requestDienThoai->thiet_ke,
                    'chat_lieu' => $requestDienThoai->chat_lieu,
                    'kich_thuoc_khoi_luong' => $requestDienThoai->kich_thuoc_khoi_luong,
                    'thoi_diem_ra_mat' => $requestDienThoai->thoi_diem_ra_mat,
                    'hang' => $requestDienThoai->hang,
                ]);
                break;

            case 3: // Đồng hồ
                $thongSoDongHo = ThongSoDongHo::create([
                    'san_pham_id' => $sanPham->id,
                    'cong_nghe_man_hinh' => $requestDongHo->cong_nghe_man_hinh,
                    'kich_thuoc_man_hinh' => $requestDongHo->kich_thuoc_man_hinh,
                    'do_phan_giai' => $requestDongHo->do_phan_giai,
                    'kich_thuoc_mat' => $requestDongHo->kich_thuoc_mat,
                    'chat_lieu_mat' => $requestDongHo->chat_lieu_mat,
                    'chat_lieu_khung_vien' => $requestDongHo->chat_lieu_khung_vien,
                    'chat_lieu_day' => $requestDongHo->chat_lieu_day,
                    'do_rong_day' => $requestDongHo->do_rong_day,
                    'do_dai_day' => $requestDongHo->do_dai_day,
                    'kha_nang_thay_day' => $requestDongHo->kha_nang_thay_day,
                    'mon_the_thao' => $requestDongHo->mon_the_thao,
                    'ho_tro_ngoai_ghi' => $requestDongHo->ho_tro_ngoai_ghi,
                    'tien_ich_dac_biet' => $requestDongHo->tien_ich_dac_biet,
                    'chong_nuoc' => $requestDongHo->chong_nuoc,
                    'theo_doi_suc_khoe' => $requestDongHo->theo_doi_suc_khoe,
                    'tien_ich_khac' => $requestDongHo->tien_ich_khac,
                    'hien_thi_thong_bao' => $requestDongHo->hien_thi_thong_bao,
                    'thoi_gian_su_dung_pin' => $requestDongHo->thoi_gian_su_dung_pin,
                    'thoi_gian_sac' => $requestDongHo->thoi_gian_sac,
                    'dung_luong_pin' => $requestDongHo->dung_luong_pin,
                    'cong_sac' => $requestDongHo->cong_sac,
                    'cpu' => $requestDongHo->cpu,
                    'bo_nho_trong' => $requestDongHo->bo_nho_trong,
                    'he_dieu_hanh' => $requestDongHo->he_dieu_hanh,
                    'ket_noi_he_dieu_hanh' => $requestDongHo->ket_noi_he_dieu_hanh,
                    'ung_dung_quan_ly' => $requestDongHo->ung_dung_quan_ly,
                    'ket_noi' => $requestDongHo->ket_noi,
                    'cam_bien' => $requestDongHo->cam_bien,
                    'dinh_vi' => $requestDongHo->dinh_vi,
                    'san_xuat_tai' => $requestDongHo->san_xuat_tai,
                    'thoi_diem_ra_mat' => $requestDongHo->thoi_diem_ra_mat,
                    'ngon_ngu' => $requestDongHo->ngon_ngu,
                    'hang_san_xuat' => $requestDongHo->hang_san_xuat,
                ]);

                break;

            default:
                return response()->json(['error' => 'Danh mục không hợp lệ'], 400);
        }

        $response = ['san_pham' => $sanPham];

        // Chỉ trả về thông số nếu không rỗng
        if ($thongSoDienThoai) {
            $response['thong_so_dien_thoai'] = $thongSoDienThoai;
        }
        if ($thongSoDongHo) {
            $response['thong_so_dong_ho'] = $thongSoDongHo;
        }
        if ($thongSoMayTinh) {
            $response['thong_so_may_tinh'] = $thongSoMayTinh;
        }

        return response()->json($response, 201);

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
    $bienTheSanPhams = BienTheSanPham::where('san_pham_id', $id)->get(['ten_bien_the', 'gia_tri_bien_the', 'gia']);

    // Lấy danh mục con và danh mục cha
    $danhMucCon = DanhMucCon::with('danhMuc')->where('id', $sanPham->danh_muc_con_id)->first(); // Giả sử `sanPham` có trường `danh_muc_con_id`

    // Kiểm tra nếu danh mục con tồn tại và lấy tên danh mục con cùng tên danh mục cha
    $tenDanhMucCon = $danhMucCon ? $danhMucCon->ten_danh_muc_con : null;
    $tenDanhMuc = $danhMucCon && $danhMucCon->danhMuc ? $danhMucCon->danhMuc->ten_danh_muc : null;

    // Lấy thông số kỹ thuật tương ứng với danh mục sản phẩm
    $thongSo = null;
    if ($sanPham->danh_muc_id == 2) {  // Điện thoại
        // Lấy thông số điện thoại nếu có
        $thongSo = $sanPham->thongSoDienThoai ? array_filter($sanPham->thongSoDienThoai->toArray(), fn($value) => $value !== null) : null;
    } elseif ($sanPham->danh_muc_id == 3) {  // Đồng hồ
        // Lấy thông số đồng hồ nếu có
        $thongSo = $sanPham->thongSoDongHo ? array_filter($sanPham->thongSoDongHo->toArray(), fn($value) => $value !== null) : null;
    } elseif ($sanPham->danh_muc_id == 1) {  // Máy tính
        // Lấy thông số máy tính nếu có
        $thongSo = $sanPham->thongSoMayTinh ? array_filter($sanPham->thongSoMayTinh->toArray(), fn($value) => $value !== null) : null;
    }

    // Return the product details along with variants and specifications
    return response()->json([
        'sanPham' => $sanPham,
        'ten_danh_muc' => $tenDanhMuc ,
        'ten_danh_muc_con' => $tenDanhMucCon,
        'bienTheSanPhams' => $bienTheSanPhams,
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

    public function updateProduct(Request $request, $id, StoreThongSoMayTinhRequest $requestMayTinh, StoreThongSoDongHoRequest $requestDongHo, StoreThongSoDienThoaiRequest $requestDienThoai)
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

        // Tìm sản phẩm cũ
        $sanPham = SanPham::find($id);
        if (!$sanPham) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        // Lưu danh mục cũ để kiểm tra
        $oldDanhMucId = $sanPham->danh_muc_id;

        // Cập nhật thông tin sản phẩm
        $sanPham->update($request->all());

        $thongSoDienThoai = [];
        $thongSoMayTinh = [];
        $thongSoDongHo = [];

        // Kiểm tra nếu thay đổi danh mục
        if ($request->danh_muc_id != $oldDanhMucId) {
            // Xóa thông số kỹ thuật cũ
            $this->deleteOldTechnicalSpecs($oldDanhMucId, $sanPham->id);

            // Thêm thông số kỹ thuật mới theo danh mục
            switch ($request->danh_muc_id) {
                case 1: // Máy tính
                    $thongSoMayTinh = ThongSoMayTinh::create([
                        'san_pham_id' => $sanPham->id,
                        'cong_nghe_cpu' => $requestMayTinh->cong_nghe_cpu,
                        'so_nhan' => $requestMayTinh->so_nhan,
                        'so_luong_luong' => $requestMayTinh->so_luong_luong,
                        'toc_do_cpu' => $requestMayTinh->toc_do_cpu,
                        'toc_do_toi_da' => $requestMayTinh->toc_do_toi_da,
                        'bo_nho_cache' => $requestMayTinh->bo_nho_cache,
                        'ram' => $requestMayTinh->ram,
                        'loai_ram' => $requestMayTinh->loai_ram,
                        'toc_do_bus_ram' => $requestMayTinh->toc_do_bus_ram,
                        'ho_tro_ram_toi_da' => $requestMayTinh->ho_tro_ram_toi_da,
                        'o_cung' => $requestMayTinh->o_cung,
                        'man_hinh' => $requestMayTinh->man_hinh,
                        'do_phan_giai' => $requestMayTinh->do_phan_giai,
                        'tan_so_quet' => $requestMayTinh->tan_so_quet,
                        'cong_nghe_man_hinh' => $requestMayTinh->cong_nghe_man_hinh,
                        'card_do_hoa' => $requestMayTinh->card_do_hoa,
                        'cong_nghe_am_thanh' => $requestMayTinh->cong_nghe_am_thanh,
                        'cong_giao_tiep' => $requestMayTinh->cong_giao_tiep,
                        'ket_noi_khong_day' => $requestMayTinh->ket_noi_khong_day,
                        'webcam' => $requestMayTinh->webcam,
                        'tinh_nang_khac' => $requestMayTinh->tinh_nang_khac,
                        'den_ban_phim' => $requestMayTinh->den_ban_phim,
                        'khoi_luong' => $requestMayTinh->khoi_luong,
                        'thoi_diem_ra_mat' => $requestMayTinh->thoi_diem_ra_mat,
                    ]);
                    break;

                case 2: // Điện thoại
                    $thongSoDienThoai = ThongSoDienThoai::create([
                        'san_pham_id' => $sanPham->id,
                        'he_dieu_hanh' => $requestDienThoai->he_dieu_hanh,
                        'chip_xu_ly' => $requestDienThoai->chip_xu_ly,
                        'toc_do_cpu' => $requestDienThoai->toc_do_cpu,
                        'chip_do_hoa' => $requestDienThoai->chip_do_hoa,
                        'ram' => $requestDienThoai->ram,
                        'dung_luong_luu_tru' => $requestDienThoai->dung_luong_luu_tru,
                        'dung_luong_con_lai' => $requestDienThoai->dung_luong_con_lai,
                        'the_nho' => $requestDienThoai->the_nho,
                        'danh_ba' => $requestDienThoai->danh_ba,
                        'camera_sau_resolution' => $requestDienThoai->camera_sau_resolution,
                        'camera_sau_video' => $requestDienThoai->camera_sau_video,
                        'camera_sau_flash' => $requestDienThoai->camera_sau_flash,
                        'camera_sau_tinh_nang' => $requestDienThoai->camera_sau_tinh_nang,
                        'camera_truoc_resolution' => $requestDienThoai->camera_truoc_resolution,
                        'camera_truoc_tinh_nang' => $requestDienThoai->camera_truoc_tinh_nang,
                        'cong_nghe_man_hinh' => $requestDienThoai->cong_nghe_man_hinh,
                        'man_hinh_resolution' => $requestDienThoai->man_hinh_resolution,
                        'man_hinh_rong' => $requestDienThoai->man_hinh_rong,
                        'man_hinh_do_sang_max' => $requestDienThoai->man_hinh_do_sang_max,
                        'mat_kinh_cam_ung' => $requestDienThoai->mat_kinh_cam_ung,
                        'dung_luong_pin' => $requestDienThoai->dung_luong_pin,
                        'loai_pin' => $requestDienThoai->loai_pin,
                        'sac_toi_da' => $requestDienThoai->sac_toi_da,
                        'sac_kem_theo' => $requestDienThoai->sac_kem_theo,
                        'cong_nghe_pin' => $requestDienThoai->cong_nghe_pin,
                        'bao_mat_nang_cao' => $requestDienThoai->bao_mat_nang_cao,
                        'tinh_nang_dac_biet' => $requestDienThoai->tinh_nang_dac_biet,
                        'khang_nuoc_bui' => $requestDienThoai->khang_nuoc_bui,
                        'ghi_am' => $requestDienThoai->ghi_am,
                        'radio' => $requestDienThoai->radio,
                        'xem_phim' => $requestDienThoai->xem_phim,
                        'nghe_nhac' => $requestDienThoai->nghe_nhac,
                        'mang_di_dong' => $requestDienThoai->mang_di_dong,
                        'sim' => $requestDienThoai->sim,
                        'wifi' => $requestDienThoai->wifi,
                        'gps' => $requestDienThoai->gps,
                        'bluetooth' => $requestDienThoai->bluetooth,
                        'cong_ket_noi_sac' => $requestDienThoai->cong_ket_noi_sac,
                        'jack_tai_nghe' => $requestDienThoai->jack_tai_nghe,
                        'ket_noi_khac' => $requestDienThoai->ket_noi_khac,
                        'thiet_ke' => $requestDienThoai->thiet_ke,
                        'chat_lieu' => $requestDienThoai->chat_lieu,
                        'kich_thuoc_khoi_luong' => $requestDienThoai->kich_thuoc_khoi_luong,
                        'thoi_diem_ra_mat' => $requestDienThoai->thoi_diem_ra_mat,
                        'hang' => $requestDienThoai->hang,
                    ]);
                    break;

                case 3: // Đồng hồ
                    $thongSoDongHo = ThongSoDongHo::create([
                        'san_pham_id' => $sanPham->id,
                        'cong_nghe_man_hinh' => $requestDongHo->cong_nghe_man_hinh,
                        'kich_thuoc_man_hinh' => $requestDongHo->kich_thuoc_man_hinh,
                        'do_phan_giai' => $requestDongHo->do_phan_giai,
                        'kich_thuoc_mat' => $requestDongHo->kich_thuoc_mat,
                        'chat_lieu_mat' => $requestDongHo->chat_lieu_mat,
                        'chat_lieu_khung_vien' => $requestDongHo->chat_lieu_khung_vien,
                        'chat_lieu_day' => $requestDongHo->chat_lieu_day,
                        'do_rong_day' => $requestDongHo->do_rong_day,
                        'do_dai_day' => $requestDongHo->do_dai_day,
                        'kha_nang_thay_day' => $requestDongHo->kha_nang_thay_day,
                        'mon_the_thao' => $requestDongHo->mon_the_thao,
                        'ho_tro_ngoai_ghi' => $requestDongHo->ho_tro_ngoai_ghi,
                        'tien_ich_dac_biet' => $requestDongHo->tien_ich_dac_biet,
                        'chong_nuoc' => $requestDongHo->chong_nuoc,
                        'theo_doi_suc_khoe' => $requestDongHo->theo_doi_suc_khoe,
                        'tien_ich_khac' => $requestDongHo->tien_ich_khac,
                        'hien_thi_thong_bao' => $requestDongHo->hien_thi_thong_bao,
                        'thoi_gian_su_dung_pin' => $requestDongHo->thoi_gian_su_dung_pin,
                        'thoi_gian_sac' => $requestDongHo->thoi_gian_sac,
                        'dung_luong_pin' => $requestDongHo->dung_luong_pin,
                        'cong_sac' => $requestDongHo->cong_sac,
                        'cpu' => $requestDongHo->cpu,
                        'bo_nho_trong' => $requestDongHo->bo_nho_trong,
                        'he_dieu_hanh' => $requestDongHo->he_dieu_hanh,
                        'ket_noi_he_dieu_hanh' => $requestDongHo->ket_noi_he_dieu_hanh,
                        'ung_dung_quan_ly' => $requestDongHo->ung_dung_quan_ly,
                        'ket_noi' => $requestDongHo->ket_noi,
                        'cam_bien' => $requestDongHo->cam_bien,
                        'dinh_vi' => $requestDongHo->dinh_vi,
                        'san_xuat_tai' => $requestDongHo->san_xuat_tai,
                        'thoi_diem_ra_mat' => $requestDongHo->thoi_diem_ra_mat,
                        'ngon_ngu' => $requestDongHo->ngon_ngu,
                        'hang_san_xuat' => $requestDongHo->hang_san_xuat,
                    ]);
                    break;
            }
        } else {
            // Nếu không thay đổi danh mục, chỉ cần thêm thông số kỹ thuật của sản phẩm mới.
            switch ($sanPham->danh_muc_id) {
                case 1:
                    $thongSoMayTinh = ThongSoMayTinh::where('san_pham_id', $sanPham->id)->first();

                    $thongSoMayTinh->update([
                        'san_pham_id' => $sanPham->id,
                        'cong_nghe_cpu' => $requestMayTinh->cong_nghe_cpu,
                        'so_nhan' => $requestMayTinh->so_nhan,
                        'so_luong_luong' => $requestMayTinh->so_luong_luong,
                        'toc_do_cpu' => $requestMayTinh->toc_do_cpu,
                        'toc_do_toi_da' => $requestMayTinh->toc_do_toi_da,
                        'bo_nho_cache' => $requestMayTinh->bo_nho_cache,
                        'ram' => $requestMayTinh->ram,
                        'loai_ram' => $requestMayTinh->loai_ram,
                        'toc_do_bus_ram' => $requestMayTinh->toc_do_bus_ram,
                        'ho_tro_ram_toi_da' => $requestMayTinh->ho_tro_ram_toi_da,
                        'o_cung' => $requestMayTinh->o_cung,
                        'man_hinh' => $requestMayTinh->man_hinh,
                        'do_phan_giai' => $requestMayTinh->do_phan_giai,
                        'tan_so_quet' => $requestMayTinh->tan_so_quet,
                        'cong_nghe_man_hinh' => $requestMayTinh->cong_nghe_man_hinh,
                        'card_do_hoa' => $requestMayTinh->card_do_hoa,
                        'cong_nghe_am_thanh' => $requestMayTinh->cong_nghe_am_thanh,
                        'cong_giao_tiep' => $requestMayTinh->cong_giao_tiep,
                        'ket_noi_khong_day' => $requestMayTinh->ket_noi_khong_day,
                        'webcam' => $requestMayTinh->webcam,
                        'tinh_nang_khac' => $requestMayTinh->tinh_nang_khac,
                        'den_ban_phim' => $requestMayTinh->den_ban_phim,
                        'khoi_luong' => $requestMayTinh->khoi_luong,
                        'thoi_diem_ra_mat' => $requestMayTinh->thoi_diem_ra_mat,

                    ]);
                    break;
                case 2:
                    $thongSoDienThoai = ThongSoDienThoai::where('san_pham_id', $sanPham->id)->first();

                    $thongSoDienThoai->update([
                        'san_pham_id' => $sanPham->id,
                        'he_dieu_hanh' => $requestDienThoai->he_dieu_hanh,
                        'chip_xu_ly' => $requestDienThoai->chip_xu_ly,
                        'toc_do_cpu' => $requestDienThoai->toc_do_cpu,
                        'chip_do_hoa' => $requestDienThoai->chip_do_hoa,
                        'ram' => $requestDienThoai->ram,
                        'dung_luong_luu_tru' => $requestDienThoai->dung_luong_luu_tru,
                        'dung_luong_con_lai' => $requestDienThoai->dung_luong_con_lai,
                        'the_nho' => $requestDienThoai->the_nho,
                        'danh_ba' => $requestDienThoai->danh_ba,
                        'camera_sau_resolution' => $requestDienThoai->camera_sau_resolution,
                        'camera_sau_video' => $requestDienThoai->camera_sau_video,
                        'camera_sau_flash' => $requestDienThoai->camera_sau_flash,
                        'camera_sau_tinh_nang' => $requestDienThoai->camera_sau_tinh_nang,
                        'camera_truoc_resolution' => $requestDienThoai->camera_truoc_resolution,
                        'camera_truoc_tinh_nang' => $requestDienThoai->camera_truoc_tinh_nang,
                        'cong_nghe_man_hinh' => $requestDienThoai->cong_nghe_man_hinh,
                        'man_hinh_resolution' => $requestDienThoai->man_hinh_resolution,
                        'man_hinh_rong' => $requestDienThoai->man_hinh_rong,
                        'man_hinh_do_sang_max' => $requestDienThoai->man_hinh_do_sang_max,
                        'mat_kinh_cam_ung' => $requestDienThoai->mat_kinh_cam_ung,
                        'dung_luong_pin' => $requestDienThoai->dung_luong_pin,
                        'loai_pin' => $requestDienThoai->loai_pin,
                        'sac_toi_da' => $requestDienThoai->sac_toi_da,
                        'sac_kem_theo' => $requestDienThoai->sac_kem_theo,
                        'cong_nghe_pin' => $requestDienThoai->cong_nghe_pin,
                        'bao_mat_nang_cao' => $requestDienThoai->bao_mat_nang_cao,
                        'tinh_nang_dac_biet' => $requestDienThoai->tinh_nang_dac_biet,
                        'khang_nuoc_bui' => $requestDienThoai->khang_nuoc_bui,
                        'ghi_am' => $requestDienThoai->ghi_am,
                        'radio' => $requestDienThoai->radio,
                        'xem_phim' => $requestDienThoai->xem_phim,
                        'nghe_nhac' => $requestDienThoai->nghe_nhac,
                        'mang_di_dong' => $requestDienThoai->mang_di_dong,
                        'sim' => $requestDienThoai->sim,
                        'wifi' => $requestDienThoai->wifi,
                        'gps' => $requestDienThoai->gps,
                        'bluetooth' => $requestDienThoai->bluetooth,
                        'cong_ket_noi_sac' => $requestDienThoai->cong_ket_noi_sac,
                        'jack_tai_nghe' => $requestDienThoai->jack_tai_nghe,
                        'ket_noi_khac' => $requestDienThoai->ket_noi_khac,
                        'thiet_ke' => $requestDienThoai->thiet_ke,
                        'chat_lieu' => $requestDienThoai->chat_lieu,
                        'kich_thuoc_khoi_luong' => $requestDienThoai->kich_thuoc_khoi_luong,
                        'thoi_diem_ra_mat' => $requestDienThoai->thoi_diem_ra_mat,
                        'hang' => $requestDienThoai->hang,

                    ]);
                    break;
                case 3:
                    $thongSoDongHo = ThongSoDongHo::where('san_pham_id', $sanPham->id)->first();

                    $thongSoDongHo->update([
                        'san_pham_id' => $sanPham->id,
                        'cong_nghe_man_hinh' => $requestDongHo->cong_nghe_man_hinh,
                        'kich_thuoc_man_hinh' => $requestDongHo->kich_thuoc_man_hinh,
                        'do_phan_giai' => $requestDongHo->do_phan_giai,
                        'kich_thuoc_mat' => $requestDongHo->kich_thuoc_mat,
                        'chat_lieu_mat' => $requestDongHo->chat_lieu_mat,
                        'chat_lieu_khung_vien' => $requestDongHo->chat_lieu_khung_vien,
                        'chat_lieu_day' => $requestDongHo->chat_lieu_day,
                        'do_rong_day' => $requestDongHo->do_rong_day,
                        'do_dai_day' => $requestDongHo->do_dai_day,
                        'kha_nang_thay_day' => $requestDongHo->kha_nang_thay_day,
                        'mon_the_thao' => $requestDongHo->mon_the_thao,
                        'ho_tro_ngoai_ghi' => $requestDongHo->ho_tro_ngoai_ghi,
                        'tien_ich_dac_biet' => $requestDongHo->tien_ich_dac_biet,
                        'chong_nuoc' => $requestDongHo->chong_nuoc,
                        'theo_doi_suc_khoe' => $requestDongHo->theo_doi_suc_khoe,
                        'tien_ich_khac' => $requestDongHo->tien_ich_khac,
                        'hien_thi_thong_bao' => $requestDongHo->hien_thi_thong_bao,
                        'thoi_gian_su_dung_pin' => $requestDongHo->thoi_gian_su_dung_pin,
                        'thoi_gian_sac' => $requestDongHo->thoi_gian_sac,
                        'dung_luong_pin' => $requestDongHo->dung_luong_pin,
                        'cong_sac' => $requestDongHo->cong_sac,
                        'cpu' => $requestDongHo->cpu,
                        'bo_nho_trong' => $requestDongHo->bo_nho_trong,
                        'he_dieu_hanh' => $requestDongHo->he_dieu_hanh,
                        'ket_noi_he_dieu_hanh' => $requestDongHo->ket_noi_he_dieu_hanh,
                        'ung_dung_quan_ly' => $requestDongHo->ung_dung_quan_ly,
                        'ket_noi' => $requestDongHo->ket_noi,
                        'cam_bien' => $requestDongHo->cam_bien,
                        'dinh_vi' => $requestDongHo->dinh_vi,
                        'san_xuat_tai' => $requestDongHo->san_xuat_tai,
                        'thoi_diem_ra_mat' => $requestDongHo->thoi_diem_ra_mat,
                        'ngon_ngu' => $requestDongHo->ngon_ngu,
                        'hang_san_xuat' => $requestDongHo->hang_san_xuat,

                    ]);
                    break;
            }
        }

        $response = ['san_pham' => $sanPham];

        // Chỉ trả về thông số nếu không rỗng
        if ($thongSoDienThoai) {
            $response['thong_so_dien_thoai'] = $thongSoDienThoai;
        }
        if ($thongSoDongHo) {
            $response['thong_so_dong_ho'] = $thongSoDongHo;
        }
        if ($thongSoMayTinh) {
            $response['thong_so_may_tinh'] = $thongSoMayTinh;
        }

        return response()->json($response, 201);

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
