<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BienTheSanPham;
use App\Models\ChiTietGioHang;
use App\Models\GioHang;
use App\Models\KhachHang;
use App\Models\SaleSanPham;
use App\Models\SanPham;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GioHangController extends Controller
{
    // Xem giỏ hàng của khách hàng
    public function viewCart($khach_hang_id)
    {
        $gioHang = GioHang::where('khach_hang_id', $khach_hang_id)
            ->with([
                'chiTietGioHangs.sanPham',
                'chiTietGioHangs.bienTheSanPham',
                'chiTietGioHangs.bienTheSanPham.lienKetBienTheVaGiaTri.giaTriThuocTinh', // Lấy giaTriThuocTinh từ lienKetBienTheVaGiaTri
                'chiTietGioHangs.bienTheSanPham.lienKetBienTheVaGiaTri.hinhAnhSanPhams',
            ])
            ->first();

        if (!$gioHang) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);
        }

        $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');
        $sanPham = [];

        $gioHang->chiTietGioHangs->each(function ($chiTiet) use ($currentDate, &$sanPham) {
            $sanPhamData = $chiTiet->sanPham;
            $bienThe = $chiTiet->bienTheSanPham;

            if (!$sanPhamData || !$bienThe) {
                return;
            }

            // Giải mã thuộc tính nếu có
            if ($chiTiet->thuoc_tinh) {
                $chiTiet->thuoc_tinh = json_decode($chiTiet->thuoc_tinh, true);
            }

            // Lấy các liên kết hình ảnh từ lienKetBienTheVaGiaTri
            $imageLinks = $bienThe->lienKetBienTheVaGiaTri->flatMap(function ($link) {
                return $link->hinhAnhSanPhams->pluck('duong_dan_hinh_anh');
            });

            // Lấy giá trị thuộc tính từ lienKetBienTheVaGiaTri, bao gồm giaTriThuocTinh_id
            $bienTheValues = $bienThe->lienKetBienTheVaGiaTri->map(function ($link) {
                return [
                    'ten_gia_tri' => $link->giaTriThuocTinh->ten_gia_tri ?? null,
                    'gia_tri_thuoc_tinh_id' => $link->giaTriThuocTinh->id ?? null, // Lấy gia_tri_thuoc_tinh_id
                ];
            })->filter()->toArray();

            // Tính toán giảm giá và giá sau khi áp dụng sale
            $sale = SaleSanPham::where('san_pham_id', $sanPhamData->id)
                ->where('ngay_bat_dau_sale', '<=', $currentDate)
                ->where('ngay_ket_thuc_sale', '>=', $currentDate)
                ->first();

            $salePercentage = $sale ? $sale->sale_theo_phan_tram : 0;
            $giaSauSale = $sanPhamData->gia * (1 - $salePercentage / 100);
            $giaSauSaleThemGiaBienThe = $giaSauSale + $bienThe->gia;

            // Tính tổng tiền cho sản phẩm
            $tongTien = $giaSauSaleThemGiaBienThe * $chiTiet->so_luong;

            // Thêm chi tiết sản phẩm vào mảng sanPham
            $productDetails = [
                'chi_tiet_id' => $chiTiet->id,  // Thêm ID của chi tiết giỏ hàng
                'san_pham_id' => $sanPhamData->id,
                'bien_the_san_pham_id' => $bienThe->id, // Thêm bien_the_san_pham_id vào đây
                'bien_the' => $bienTheValues, // Đưa ten_gia_tri vào bien_the và gia_tri_thuoc_tinh_id
                'ten_san_pham' => $sanPhamData->ten_san_pham,
                'gia_goc' => $sanPhamData->gia,
                'gia_sau_sale' => $giaSauSale,
                'gia_sau_sale_them_gia_bien_the' => $giaSauSaleThemGiaBienThe,
                'tong_tien' => $tongTien,
                'so_luong' => $chiTiet->so_luong,
                'images' => $imageLinks->toArray(),
            ];

            // Thêm chi tiết sản phẩm vào mảng chung
            $sanPham[] = $productDetails;
        });

        return response()->json(
            [
                'gio_hang_id' => $gioHang->id,
                'tong_san_pham' => count($sanPham),
                'san_pham' => $sanPham,
            ],
            200
        );
    }




    // Thêm sản phẩm vào giỏ hàng
    public function addProduct(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'bien_the_san_pham_id' => 'required|exists:bien_the_san_phams,id',
            'attributes' => 'required|array',
            'attributes.*.gia_tri_thuoc_tinh_id' => 'required|exists:gia_tri_thuoc_tinhs,id',
            'attributes.*.ten_gia_tri' => 'required|string',
            'gia' => 'required|numeric|min:0'
        ]);

        // Sử dụng transaction để đảm bảo dữ liệu đồng bộ
        $gioHang = DB::transaction(function () use ($validatedData) {
            // Tìm hoặc tạo giỏ hàng cho khách hàng
            $gioHang = GioHang::firstOrCreate([
                'khach_hang_id' => $validatedData['khach_hang_id'],
                'trang_thai' => 'chua_thanh_toan'
            ]);

            // Lưu thuộc tính dưới dạng JSON
            $thuocTinh = json_encode($validatedData['attributes']); // Chuyển attributes thành JSON

            // Lấy chi tiết giỏ hàng liên quan
            $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $gioHang->id)
                ->where('san_pham_id', $validatedData['san_pham_id'])
                ->where('bien_the_san_pham_id', $validatedData['bien_the_san_pham_id'])
                ->get()
                ->first(function ($item) use ($thuocTinh) {
                    // So sánh thuộc tính (JSON)
                    return json_decode($item->thuoc_tinh, true) == json_decode($thuocTinh, true);
                });

            if ($chiTietGioHang) {
                // Nếu sản phẩm đã tồn tại, cập nhật số lượng và giá
                $chiTietGioHang->so_luong += $validatedData['so_luong'];
                $chiTietGioHang->gia += $validatedData['gia'] * $validatedData['so_luong'];
                $chiTietGioHang->save();
            } else {
                // Nếu sản phẩm chưa tồn tại, thêm mới vào giỏ hàng
                ChiTietGioHang::create([
                    'gio_hang_id' => $gioHang->id,
                    'san_pham_id' => $validatedData['san_pham_id'],
                    'bien_the_san_pham_id' => $validatedData['bien_the_san_pham_id'],
                    'so_luong' => $validatedData['so_luong'],
                    'gia' => $validatedData['gia'] * $validatedData['so_luong'],
                    'thuoc_tinh' => $thuocTinh,
                ]);
            }

            return $gioHang;
        });

        return response()->json([
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công',
            'gio_hang' => $gioHang->load('chiTietGioHangs.sanPham', 'chiTietGioHangs.bienTheSanPham')
        ], 200);
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateProduct(Request $request): JsonResponse
    {
        $request->validate([
            'chi_tiet_gio_hang_id' => 'required|exists:chi_tiet_gio_hangs,id',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0'
        ]);

        $chiTietGioHang = ChiTietGioHang::find($request->chi_tiet_gio_hang_id);

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Chi tiết giỏ hàng không tồn tại'], 404);
        }
        // // Cập nhật chi tiết giỏ hàng
        $chiTietGioHang->so_luong = $request->so_luong;
        $chiTietGioHang->gia = $request->gia * $chiTietGioHang->so_luong;
        $chiTietGioHang->save();

        return response()->json([
            'message' => 'Cập nhật số lượng sản phẩm thành công',
            'chi_tiet_gio_hang' => $chiTietGioHang,

        ], 200);
    }
    // Xóa sản phẩm khỏi giỏ hàng
    public function removeProduct($chi_tiet_gio_hang_id)
    {
        // Kiểm tra xem chi tiết giỏ hàng có tồn tại trong cơ sở dữ liệu hay không
        $chiTietGioHang = ChiTietGioHang::find($chi_tiet_gio_hang_id);

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

        // Tiến hành xóa sản phẩm khỏi giỏ hàng
        $chiTietGioHang->delete();

        return response()->json(['message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'], 200);
    }

    public function xoaGioHang($khach_hang_id): JsonResponse
    {
        // Tìm giỏ hàng của khách hàng
        $gioHang = GioHang::where('khach_hang_id', $khach_hang_id)->first();

        if (!$gioHang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy giỏ hàng của khách hàng.'
            ], 404);
        }
        // Xóa tất cả chi tiết giỏ hàng dựa trên gio_hang_id
        ChiTietGioHang::where('gio_hang_id', $gioHang->id)->delete();
        // Xóa giỏ hàng của khách hàng
        $gioHang->delete();

        // Xóa tất cả chi tiết giỏ hàng dựa trên gio_hang_id
        ChiTietGioHang::where('gio_hang_id', $gioHang->id)->delete();

        // Xóa giỏ hàng của khách hàng
        $gioHang->delete();


        return response()->json([
            'status' => 'success',
            'message' => 'Giỏ hàng và chi tiết giỏ hàng của khách hàng đã được xóa thành công.'
        ], 200);
    }
}
