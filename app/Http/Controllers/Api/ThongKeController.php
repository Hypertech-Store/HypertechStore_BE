<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\KhachHang;
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
        $tong_don_hang_dang_giu = DonHang::whereNotIn('trang_thai_don_hang_id', [2, 4])
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
        // Set default values for 'month' and 'year' or use the current month/year.
        $thang = (int)$request->query('month', now()->month); // Mặc định là tháng hiện tại
        $nam = (int)$request->query('year', now()->year); // Mặc định là năm hiện tại

        // Fetch distinct years from the database to populate the year select
        $availableYears = ChiTietDonHang::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year'); // Get a list of years to populate the select

        // Lấy danh sách các ngày trong tháng hiện tại
        $daysInCurrentMonth = collect(range(1, Carbon::createFromDate($nam, $thang, 1)->daysInMonth))
            ->map(function ($day) use ($thang, $nam) {
                // Create a Carbon instance for the given day, month, and year
                return Carbon::createFromDate($nam, $thang, $day)->format('Y-m-d');
            });

        // Dữ liệu của tháng hiện tại
        $currentMonthData = ChiTietDonHang::selectRaw('DATE(created_at) as ngay, SUM(so_luong) as tong_san_pham')
            ->whereMonth('created_at', $thang)
            ->whereYear('created_at', $nam)
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay');

        // Lấp đầy dữ liệu cho tháng hiện tại
        $currentMonthData = $daysInCurrentMonth->map(function ($day) use ($currentMonthData) {
            return [
                'ngay' => $day,
                'tong_san_pham' => $currentMonthData->get($day)->tong_san_pham ?? 0
            ];
        });

        // Xử lý tháng trước
        $previousMonth = $thang - 1 <= 0 ? 12 : $thang - 1;
        $previousYear = $thang - 1 <= 0 ? $nam - 1 : $nam;

        // Lấy danh sách các ngày trong tháng trước
        $daysInPreviousMonth = collect(range(1, Carbon::createFromDate($previousYear, $previousMonth, 1)->daysInMonth))
            ->map(function ($day) use ($previousMonth, $previousYear) {
                return Carbon::createFromDate($previousYear, $previousMonth, $day)->format('Y-m-d');
            });

        // Dữ liệu của tháng trước
        $previousMonthData = ChiTietDonHang::selectRaw('DATE(created_at) as ngay, SUM(so_luong) as tong_san_pham')
            ->whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay');

        // Lấp đầy dữ liệu cho tháng trước
        $previousMonthData = $daysInPreviousMonth->map(function ($day) use ($previousMonthData) {
            return [
                'ngay' => $day,
                'tong_san_pham' => $previousMonthData->get($day)->tong_san_pham ?? 0
            ];
        });

        // Return the JSON response
        return response()->json([
            'current_month' => $currentMonthData,
            'previous_month' => $previousMonthData,
            'previousMonth' => $previousMonth,
            'previousYear' => $previousYear,
            'available_years' => $availableYears, // Add available years for select
        ]);
    }






    public function thongKeDonHang7Ngay(Request $request)
    {
        $today = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay(); // Loại bỏ giờ, giữ lại ngày
        $sevenDaysAgo = $today->copy()->subDays(6); // Ngày bắt đầu 7 ngày trước

        $previousSevenDaysStart = $sevenDaysAgo->copy()->subDays(7); // Ngày bắt đầu của chu kỳ trước
        $previousSevenDaysEnd = $sevenDaysAgo->copy()->subDays(1); // Ngày kết thúc của chu kỳ trước


        $currentSevenDaysRange = $this->generateDateRange($sevenDaysAgo, $today);
        $previousSevenDaysRange = $this->generateDateRange($previousSevenDaysStart, $previousSevenDaysEnd);

        $currentSevenDaysData = DonHang::selectRaw('DATE(created_at) as ngay, COUNT(id) as tong_don_hang, SUM(CASE WHEN trang_thai_don_hang_id = 5 THEN 1 ELSE 0 END) as hoan_thanh')
            ->whereBetween('created_at', [$sevenDaysAgo, $today])
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay')
            ->toArray();

        $previousSevenDaysData = DonHang::selectRaw('DATE(created_at) as ngay, COUNT(id) as tong_don_hang, SUM(CASE WHEN trang_thai_don_hang_id = 5 THEN 1 ELSE 0 END) as hoan_thanh')
            ->whereBetween('created_at', [$previousSevenDaysStart, $previousSevenDaysEnd])
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay')
            ->toArray();

        $filledCurrentSevenDaysData = $this->fillMissingDates($currentSevenDaysRange, $currentSevenDaysData);
        $filledPreviousSevenDaysData = $this->fillMissingDates($previousSevenDaysRange, $previousSevenDaysData);

        // Chuyển đổi thành Collection để sử dụng sum()
        $totalCurrent = collect($filledCurrentSevenDaysData)->sum('tong_don_hang');
        $totalPrevious = collect($filledPreviousSevenDaysData)->sum('tong_don_hang');
        $difference = $totalCurrent - $totalPrevious;
        $percentageChange = $totalPrevious > 0 ? (($difference / $totalPrevious) * 100) : ($totalCurrent > 0 ? 100 : 0);


        $completedOrders = DonHang::where('trang_thai_don_hang_id', 5)->count();
        $totalOrders = DonHang::count(); // Tổng số đơn hàng

        // Kiểm tra nếu có đơn hàng nào
        if ($totalOrders > 0) {
            $completionPercentage = ($completedOrders / $totalOrders) * 100;
            $pendingPercentage = 100 - $completionPercentage; // Phần trăm chưa hoàn thành
        } else {
            $completionPercentage = 0;
            $pendingPercentage = 0;
        }

        return response()->json([
            'current_seven_days_data' => $filledCurrentSevenDaysData,
            'previous_seven_days_data' => $filledPreviousSevenDaysData,
            'ti_le_chenh_lech' => $percentageChange,
            'ti_le_hoan_thanh' => round($completionPercentage, 2),
            'ti_le_chua_hoan_thanh' => round($pendingPercentage, 2),
            'tong_don_hang' => $totalOrders,
            'currentSevenDaysData' => $currentSevenDaysData,
            'today' => $today,
            'sevenDaysAgo' => $sevenDaysAgo,
            'previousSevenDaysStart' => $previousSevenDaysStart,
            'previousSevenDaysEnd' => $previousSevenDaysEnd
        ]);
    }

    public function thongKeKhachHangMoi7Ngay(Request $request)
    {
        $today = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $sevenDaysAgo = $today->copy()->subDays(6);

        $previousSevenDaysStart = $sevenDaysAgo->copy()->subDays(7);
        $previousSevenDaysEnd = $sevenDaysAgo->copy()->subDays(1);

        $currentSevenDaysRange = $this->generateDateRange($sevenDaysAgo, $today);
        $previousSevenDaysRange = $this->generateDateRange($previousSevenDaysStart, $previousSevenDaysEnd);

        // Lấy dữ liệu khách hàng trong 7 ngày gần đây
        $currentFourteenDaysData = KhachHang::selectRaw('DATE(created_at) as ngay, COUNT(id) as tong_khach_hang')
            ->whereBetween('created_at', [$sevenDaysAgo, $today])
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay')
            ->toArray();

        // Lấy dữ liệu khách hàng trong 7 ngày trước đó
        $previousFourteenDaysData = KhachHang::selectRaw('DATE(created_at) as ngay, COUNT(id) as tong_khach_hang')
            ->whereBetween('created_at', [$previousSevenDaysStart, $previousSevenDaysEnd])
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay')
            ->toArray();

        // Điền ngày thiếu vào dữ liệu
        $filledCurrentFourteenDaysData = $this->fillMissDates($currentSevenDaysRange, $currentFourteenDaysData);
        $filledPreviousFourteenDaysData = $this->fillMissDates($previousSevenDaysRange, $previousFourteenDaysData);

        // Tổng số khách hàng mới trong 2 khoảng thời gian
        $totalCurrent = collect($filledCurrentFourteenDaysData)->sum('tong_khach_hang');
        $totalPrevious = collect($filledPreviousFourteenDaysData)->sum('tong_khach_hang');
        $difference = $totalCurrent - $totalPrevious;
        $percentageChange = $totalPrevious > 0 ? (($difference / $totalPrevious) * 100) : ($totalCurrent > 0 ? 100 : 0);

        $tongKhachHang = KhachHang::count();

        return response()->json([
            'today' => $today->toDateString(),
            'seven_days_ago' => $sevenDaysAgo->toDateString(),
            'previous_seven_days_start' => $previousSevenDaysStart->toDateString(),
            'previous_seven_days_end' => $previousSevenDaysEnd->toDateString(),
            'current_fourteen_days_data' => $filledCurrentFourteenDaysData,
            'previous_fourteen_days_data' => $filledPreviousFourteenDaysData,
            'ti_le_chenh_lech' => $percentageChange,
            'tong_khach_hang' => $tongKhachHang,
        ]);
    }



    /**
     * Tạo danh sách các ngày trong khoảng thời gian
     */
    private function generateDateRange($startDate, $endDate)
    {
        $dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->toDateString();
        }
        return $dates;
    }

    /**
     * Lấp đầy dữ liệu trống cho các ngày
     */
    private function fillMissingDates($dateRange, $data)
    {
        // Đảm bảo $data là Collection
        $dataCollection = collect($data);

        $filledData = [];
        foreach ($dateRange as $date) {
            $filledData[] = $dataCollection->get($date, [
                'ngay' => $date,
                'tong_don_hang' => 0,
                'hoan_thanh' => 0,
            ]);
        }
        return $filledData;
    }

    public function fillMissDates($dateRange, $existingData)
    {
        // Chuyển đổi dữ liệu hiện tại thành một mảng với ngày làm key
        $existingData = collect($existingData);

        // Tạo một mảng mới chứa tất cả các ngày trong khoảng thời gian
        $filledData = [];

        foreach ($dateRange as $date) {
            // Kiểm tra xem ngày có trong dữ liệu hiện tại không
            if ($existingData->has($date)) {
                // Nếu có, thêm vào mảng đã điền
                $filledData[$date] = $existingData->get($date);
            } else {
                // Nếu không, tạo dữ liệu cho ngày này là 0
                $filledData[$date] = [
                    'ngay' => $date,
                    'tong_khach_hang' => 0,
                ];
            }
        }

        return $filledData;
    }
}
