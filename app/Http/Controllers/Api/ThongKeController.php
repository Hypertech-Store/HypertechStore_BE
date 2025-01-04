<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\SanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ThongKeController extends Controller
{
    public function thongKe()
    {
        // Tổng hàng mới
        $tong_hang_moi = DonHang::where('trang_thai_don_hang_id', 1)
            ->count();

        // Tổng đơn hàng đang giữ
        $tong_don_hang_dang_giu = DonHang::whereNotIn('trang_thai_don_hang_id', [1, 5])
            ->count();

        // Tổng sản phẩm có số lượng tồn kho = 0
        $tong_san_pham_ton_kho_bang_0 = SanPham::where('so_luong_ton_kho', 0)
            ->count();

        // Trả về kết quả dạng JSON
        return response()->json([
            'tong_hang_moi' => $tong_hang_moi,
            'tong_don_hang_dang_giu' => $tong_don_hang_dang_giu,
            'tong_san_pham_ton_kho_bang_0' => $tong_san_pham_ton_kho_bang_0,
        ]);
    }

    public function thongKeSanPham(Request $request)
    {
        $thang = $request->query('month', now()->month); // Mặc định là tháng hiện tại
        $nam = $request->query('year', now()->year); // Mặc định là năm hiện tại

        // Dữ liệu của tháng hiện tại
        $currentMonthData = ChiTietDonHang::selectRaw('DATE(created_at) as ngay, SUM(so_luong) as tong_san_pham')
            ->whereMonth('created_at', $thang)
            ->whereYear('created_at', $nam)
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get();

        // Dữ liệu của tháng trước
        $previousMonth = $thang - 1 > 0 ? $thang - 1 : 12;
        $previousYear = $thang - 1 > 0 ? $nam : $nam - 1;

        $previousMonthData = ChiTietDonHang::selectRaw('DATE(created_at) as ngay, SUM(so_luong) as tong_san_pham')
            ->whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get();

        return response()->json([
            'current_month' => $currentMonthData,
            'previous_month' => $previousMonthData,
            'previousMonth' => $previousMonth,
            'previousYear' => $previousYear
        ]);
    }

    public function thongKeDonHang7Ngay(Request $request)
    {
        // Lấy ngày hiện tại và xác định 7 ngày hiện tại và 7 ngày trước
        $today = Carbon::today();
        $sevenDaysAgo = $today->copy()->subDays(7); // 7 ngày trước từ hôm nay

        // Lấy 7 ngày trước của 7 ngày hiện tại
        $previousSevenDaysStart = $sevenDaysAgo->copy()->subDays(7); // Lùi thêm 7 ngày nữa
        $previousSevenDaysEnd = $sevenDaysAgo; // Đến ngày hôm nay (hoặc ngày cuối của 7 ngày hiện tại)


        $currentSevenDaysData = DonHang::selectRaw('DATE(created_at) as ngay, COUNT(id) as tong_don_hang, SUM(CASE WHEN trang_thai_don_hang_id = 5 THEN 1 ELSE 0 END) as hoan_thanh')
            ->whereBetween('created_at', [$sevenDaysAgo, $today])
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get();



        // Trả về kết quả dưới dạng JSON
        return response()->json([
            'today' => $today->toDateString(),
            'seven_days_ago' => $sevenDaysAgo->toDateString(),
            'previous_seven_days_start' => $previousSevenDaysStart->toDateString(),
            'previous_seven_days_end' => $previousSevenDaysEnd->toDateString(),
        ]);
    }
}
