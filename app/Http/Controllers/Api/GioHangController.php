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

class GioHangController extends Controller
{
    // Xem giỏ hàng của khách hàng
    public function viewCart($khach_hang_id)
{
    $gioHang = GioHang::where('khach_hang_id', $khach_hang_id)
        ->with('chiTietGioHangs.sanPham', 'chiTietGioHangs.bienTheSanPham')
        ->first();

    if (!$gioHang) {
        return response()->json(['message' => 'Giỏ hàng không tồn tại'], 404);
    }

    $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');

    // Duyệt qua các chi tiết giỏ hàng và gắn thông tin sản phẩm vào
    $gioHang->chiTietGioHangs->each(function ($chiTiet) use ($currentDate) {
        $sanPham = $chiTiet->sanPham;
        $bienThe = $chiTiet->bienTheSanPham;

        // Tìm thông tin sale
        $sale = SaleSanPham::where('san_pham_id', $sanPham->id)
            ->where('ngay_bat_dau_sale', '<=', $currentDate)
            ->where('ngay_ket_thuc_sale', '>=', $currentDate)
            ->first();

        $salePercentage = $sale ? $sale->sale_theo_phan_tram : null;

        // Tính giá sau giảm
        $giaSauSale = $salePercentage
            ? $sanPham->gia * (1 - $salePercentage / 100)
            : $sanPham->gia;

        // Tính tổng tiền
        $giaSauSaleThemGiaBienThe = $giaSauSale + $bienThe->gia;
        $tongTien = $giaSauSaleThemGiaBienThe * $chiTiet->so_luong;

        // Kiểm tra trạng thái sản phẩm
        $isNew = $sanPham->created_at >= now()->subWeek();
        $saleStatus = $isNew && $salePercentage ? 'Both' : ($isNew ? 'New' : ($salePercentage ? 'Sale' : null));

        // Gắn thêm thông tin chi tiết sản phẩm vào chi tiết giỏ hàng
        $chiTiet->chi_tiet_san_pham = [
            'san_pham_id' => $sanPham->id,
            'bien_the_san_pham_id' => $bienThe->id,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'gia_goc' => $sanPham->gia,
            'gia_sau_sale' => $giaSauSale,
            'gia_sau_sale_them_gia_bien_the' => $giaSauSaleThemGiaBienThe,
            'sale_status' => $saleStatus,
            'tong_tien' => $tongTien,
        ];
    });

    return response()->json([
        'gio_hang' => $gioHang,
    ], 200);
}



    // Thêm sản phẩm vào giỏ hàng
    public function addProduct(Request $request): JsonResponse
    {
        $request->validate([
            'khach_hang_id' => 'required|exists:khach_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'bien_the_san_pham_id' => 'required|exists:bien_the_san_phams,id', // Bắt buộc có biến thể sản phẩm
            'gia' => 'required|numeric|min:0'
        ]);

        // Kiểm tra sự tồn tại của khách hàng
        $khachHang = KhachHang::find($request->khach_hang_id);
        if (!$khachHang) {
            return response()->json(['message' => 'Khách hàng không tồn tại'], 404);
        }

        // Kiểm tra sự tồn tại của sản phẩm
        $sanPham = SanPham::find($request->san_pham_id);
        if (!$sanPham) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // Kiểm tra sự tồn tại của biến thể sản phẩm
        $bienTheSanPham = BienTheSanPham::find($request->bien_the_san_pham_id);
        if (!$bienTheSanPham || $bienTheSanPham->san_pham_id != $sanPham->id) {
            return response()->json(['message' => 'Biến thể sản phẩm không hợp lệ'], 400);
        }

        // Tìm hoặc tạo giỏ hàng cho khách hàng
        $gioHang = GioHang::firstOrCreate([
            'khach_hang_id' => $request->khach_hang_id,
            'trang_thai' => 'chua_thanh_toan'
        ]);

        // Kiểm tra nếu biến thể sản phẩm đã tồn tại trong giỏ hàng
        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $gioHang->id)
            ->where('san_pham_id', $request->san_pham_id)
            ->where('bien_the_san_pham_id', $request->bien_the_san_pham_id)
            ->first();

        if ($chiTietGioHang) {
            // Nếu biến thể sản phẩm đã có trong giỏ hàng, cập nhật số lượng
            $chiTietGioHang->so_luong += $request->so_luong; // Tăng số lượng sản phẩm hiện tại
            $chiTietGioHang->gia +=  $request->gia ;
            $chiTietGioHang->save();
        } else {
            // Nếu biến thể sản phẩm chưa có, thêm mới vào giỏ hàng
            $chiTietGioHang = ChiTietGioHang::create([
                'gio_hang_id' => $gioHang->id,
                'san_pham_id' => $request->san_pham_id,
                'bien_the_san_pham_id' => $request->bien_the_san_pham_id,
                'so_luong' => $request->so_luong,
                'gia' => $request->gia
            ]);
        }

        return response()->json([
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công',
            'gio_hang' => $gioHang->load('chiTietGioHangs.sanPham', 'chiTietGioHangs.bienTheSanPham') // Load cả thông tin biến thể
        ], 200);
    }


    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateProduct(Request $request): JsonResponse
    {
        $request->validate([
            'gio_hang_id' => 'required|exists:gio_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
            'bien_the_san_pham_id' => 'required|exists:bien_the_san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'gia_sau_sale_them_gia_bien_the' => 'required|numeric|min:0'
        ]);

        // Tìm sản phẩm trong giỏ hàng
        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $request->gio_hang_id)
            ->where('san_pham_id', $request->san_pham_id)
            ->where('bien_the_san_pham_id', $request->bien_the_san_pham_id)
            ->first();

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

        // // Cập nhật chi tiết giỏ hàng
        $chiTietGioHang->so_luong = $request->so_luong;
        $chiTietGioHang->gia = $request->gia_sau_sale_them_gia_bien_the * $chiTietGioHang->so_luong;
        $chiTietGioHang->save();

        return response()->json([
            'message' => 'Cập nhật số lượng sản phẩm thành công',
            'chi_tiet_gio_hang' => $chiTietGioHang,

        ], 200);
    }
    // Xóa sản phẩm khỏi giỏ hàng
    public function removeProduct(Request $request)
    {
        $request->validate([
            'gio_hang_id' => 'required|exists:gio_hangs,id',
            'san_pham_id' => 'required|exists:san_phams,id',
        ]);

        $chiTietGioHang = ChiTietGioHang::where('gio_hang_id', $request->gio_hang_id)
            ->where('san_pham_id', $request->san_pham_id)
            ->first();

        if (!$chiTietGioHang) {
            return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

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
