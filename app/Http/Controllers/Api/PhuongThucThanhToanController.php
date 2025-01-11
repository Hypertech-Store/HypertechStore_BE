<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePhuongThucThanhToanRequest;
use App\Models\PhuongThucThanhToan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PhuongThucThanhToanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PhuongThucThanhToan::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhuongThucThanhToanRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('image')) {
            // Upload hình ảnh và lưu đường dẫn
            $path = $request->file('image')->store('phuong_thuc_thanh_toans', 'public');
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);
            $data['anh_phuong_thuc'] = $path;
            unset($data['image']);
        }
        PhuongThucThanhToan::query()->create($data);

        return response()->json([
            'message' => 'Phương thức thanh toán được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = PhuongThucThanhToan::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết phương thức thanh toán id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy phương thức thanh toán id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa phương thức thanh toán: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy phương thức thanh toán id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Định nghĩa quy tắc xác thực
            $request->validate([
                'ten_phuong_thuc' => 'nullable|string|max:255',
                'anh_phuong_thuc' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Lấy đối tượng phương thức thanh toán từ cơ sở dữ liệu
            $data = PhuongThucThanhToan::query()->findOrFail($id);

            // Kiểm tra và xử lý ảnh nếu có
            if ($request->hasFile('anh_phuong_thuc')) {
                // Kiểm tra và xóa ảnh cũ nếu có
                if ($data->anh_phuong_thuc && Storage::exists('public/' . $data->anh_phuong_thuc)) {
                    Storage::delete('public/' . $data->anh_phuong_thuc);
                }

                // Lưu ảnh mới
                $path = $request->file('anh_phuong_thuc')->store('phuong_thuc_thanh_toans', 'public');
                Log::info('Đường dẫn hình ảnh mới:', ['path' => $path]);

                // Cập nhật đường dẫn ảnh mới vào cơ sở dữ liệu
                $data->update([
                    'anh_phuong_thuc' => $path,
                ]);
            }

            // Cập nhật các trường còn lại trong request
            $data->update($request->except('anh_phuong_thuc'));  // Tránh cập nhật lại ảnh nếu không có thay đổi

            return response()->json([
                'message' => 'Cập nhật phương thức thanh toán id = ' . $id,
                'data' => $data,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy phương thức thanh toán id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật phương thức thanh toán: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật phương thức thanh toán',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function updateStatus(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'phuong_thuc_id' => 'required|integer|exists:phuong_thuc_thanh_toans,id', // Kiểm tra phuong_thuc_id tồn tại
            'trang_thai'    => 'required|boolean', // Giá trị trạng thái phải là 0 hoặc 1
        ]);

        // Tìm khách hàng theo ID
        $data = PhuongThucThanhToan::find($validated['phuong_thuc_id']);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Phương thức phương thức không tìm thấy.',
            ], 404);
        }

        // Cập nhật trạng thái
        $data->trang_thai = $validated['trang_thai'];
        $data->save();

        // Trả về dữ liệu phương thức vận chuyển sau khi cập nhật
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => [
                'phuong_thuc_id' => $data->id,
                'trang_thai' => $data->trang_thai,
                // Thêm bất kỳ trường nào khác bạn cần đưa vào response
            ],
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            PhuongThucThanhToan::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy phương thức thanh toán id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa phương thức thanh toán: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa phương thức thanh toán',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
