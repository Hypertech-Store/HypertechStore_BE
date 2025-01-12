<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePhieuGiamGiaRequest;
use App\Models\PhieuGiamGia;
use App\Models\PhieuGiamGiaVaKhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhieuGiamGiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);  // Sử dụng query 'page' hoặc mặc định là 1
        $numberRow = $request->query('number_row', 10);  // Sử dụng query 'number_row' hoặc mặc định là 9
        $data = PhieuGiamGia::query()->paginate($numberRow, ['*'], 'page', $page);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhieuGiamGiaRequest $request)
    {
        $request['trang_thai'] = 1;
        $validated = $request->validated();

        // Tự động sinh mã giảm giá
        $validated['ma_giam_gia'] = strtoupper(Str::random(10)); // Sinh chuỗi ngẫu nhiên gồm 10 ký tự

        // Kiểm tra số lượt sử dụng, nếu = 0 thì tự động tắt trạng thái
        if ($validated['so_luot_su_dung'] == 0) {
            $validated['trang_thai'] = 0;  // Nếu số lượt sử dụng bằng 0 thì tắt trạng thái
        } else {
            $validated['trang_thai'] = 1;  // Nếu không thì bật trạng thái
        }

        // Tạo phiếu giảm giá
        $phieuGiamGia = PhieuGiamGia::create($validated);

        return response()->json([
            'message' => 'Phiếu giảm giá đã được tạo thành công.',
            'data' => $phieuGiamGia,  // Trả về thông tin của phiếu giảm giá vừa tạo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Tìm phiếu giảm giá theo ID
        $phieuGiamGia = PhieuGiamGia::find($id);

        // Kiểm tra nếu không tìm thấy
        if (!$phieuGiamGia) {
            return response()->json([
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        return response()->json([
            'data' => $phieuGiamGia,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'trang_thai' => 'required|boolean', // Giá trị trạng thái phải là 0 hoặc 1
        ]);

        // Tìm phiếu giảm giá theo ID
        $phieuGiamGia = PhieuGiamGia::find($id);

        if (!$phieuGiamGia) {
            return response()->json([
                'success' => false,
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        // Cập nhật trạng thái phiếu giảm giá
        $phieuGiamGia->trang_thai = $validated['trang_thai'];
        $phieuGiamGia->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phiếu giảm giá thành công.',
            'data' => [
                'phieu_giam_gia_id' => $phieuGiamGia->id,
                'trang_thai' => $phieuGiamGia->trang_thai,
            ],
        ], 200);
    }


    public function destroy(string $id)
    {
        $phieuGiamGia = PhieuGiamGia::find($id);

        if (!$phieuGiamGia) {
            return response()->json([
                'message' => 'Phiếu giảm giá không tồn tại.',
            ], 404);
        }

        // Xóa phiếu giảm giá
        $phieuGiamGia->delete();

        return response()->json([
            'message' => 'Phiếu giảm giá đã được xóa thành công.',
        ], 200);
    }
    public function layPhieuGiamGiaPhuHopVoiDonHang(Request $request)
    {
        // Lấy giá trị đơn hàng từ request
        $orderValue = $request['gia_tri_don_hang'];

        if (!$orderValue) {
            return response()->json([
                'message' => 'Vui lòng cung cấp giá trị đơn hàng.',
            ], 400);
        }

        // Lấy danh sách phiếu giảm giá phù hợp
        $data = PhieuGiamGia::where('gia_tri_don_hang_toi_thieu', '<=', $orderValue)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();

        return response()->json([
            'data' => $data,
        ], 200);
    }
    public function checkPhieuGiamGia(Request $request)
    {
        $maGiamGia = $request->input('ma_giam_gia');
        $khachHangId = $request->input('khach_hang_id');

        // Kiểm tra mã giảm giá
        $magiamgia = PhieuGiamGia::where('ma_giam_gia', $maGiamGia)
            ->whereDate('ngay_bat_dau', '<=', now())
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->first();

        if (!$magiamgia) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn',
            ], 404);
        }

        // Kiểm tra xem khách hàng đã sử dụng mã này chưa
        $daSuDung = PhieuGiamGiaVaKhachHang::where('phieu_giam_gia_id', $magiamgia->id)
            ->where('khach_hang_id', $khachHangId)
            ->exists();

        if ($daSuDung) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã sử dụng mã giảm giá này trước đây',
            ], 403);
        }

        // Mã giảm giá hợp lệ và chưa được sử dụng
        return response()->json([
            'success' => true,
            'message' => 'Mã giảm giá hợp lệ',
            'data' => $magiamgia,
        ], 200);
    }
}
