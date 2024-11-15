<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleSanPham;
use App\Models\SanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleSanPhamController extends Controller
{
    public function addSale(Request $request)
    {
        // Validate đầu vào
        $request->validate([
            'san_pham_id' => 'required|exists:san_phams,id',
            'sale_theo_phan_tram' => 'required|numeric|min:0|max:100',
            'ngay_bat_dau_sale' => 'required|date',
            'ngay_ket_thuc_sale' => 'required|date|after:start_date',
        ]);

        // Lấy sản phẩm từ ID
        $sanPham = SanPham::find($request->san_pham_id);

        // Tạo bản ghi mới cho PriceSale
        $sale_san_pham = SaleSanPham::create([
            'san_pham_id' => $request->san_pham_id,
            'sale_theo_phan_tram' => $request->sale_theo_phan_tram,
            'ngay_bat_dau_sale' => $request->ngay_bat_dau_sale,
            'ngay_ket_thuc_sale' => $request->ngay_ket_thuc_sale,
        ]);

        return response()->json([
            'message' => 'Sản phẩm sale đã được thêm thành công.',
            'data' => [
                'sale_san_pham' => $sale_san_pham,
            ]

        ], Response::HTTP_CREATED);
    }

    public function getSaleSanPhams(Request $request)
    {
        // Lấy ngày hiện tại
        $currentDate = Carbon::now();

        // Lấy các sản phẩm sale còn hiệu lực
        $saleSanPhams = SaleSanPham::where('ngay_bat_dau_sale', '<=', $currentDate)
            ->where('ngay_ket_thuc_sale', '>=', $currentDate)
            ->with('sanPham')
            ->get();

        // Nếu không có sản phẩm sale nào
        if ($saleSanPhams->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào đang trong chương trình sale.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Danh sách sản phẩm sale',
            'data' => $saleSanPhams,
        ], Response::HTTP_OK);
    }
}
