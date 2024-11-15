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
        // Lấy ngày hiện tại với thời gian đầy đủ (Ngày giờ hiện tại ở Việt Nam)
        $currentDate = Carbon::now()->timezone('Asia/Ho_Chi_Minh');

        // Lấy các sản phẩm sale còn hiệu lực (Sale hiện tại còn hiệu lực nếu ngày bắt đầu nhỏ hơn hoặc bằng hiện tại, và ngày kết thúc lớn hơn hoặc bằng hiện tại)
        $saleSanPhams = SaleSanPham::where('ngay_bat_dau_sale', '<=', $currentDate)
            ->where('ngay_ket_thuc_sale', '>=', $currentDate)
            ->with('sanPham')  // Tải thông tin sản phẩm liên quan
            ->get();

        // Kiểm tra nếu không có sản phẩm nào đang trong chương trình sale
        if ($saleSanPhams->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào đang trong chương trình sale.',
                'data' => [],  // Trả về mảng rỗng nếu không có sản phẩm
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Danh sách sản phẩm sale',
            'data' => $saleSanPhams, // Trả về dữ liệu sản phẩm sale
        ], Response::HTTP_OK);
    }
}
