<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhMucRequest;
use App\Models\DanhMuc;
use App\Models\DanhMucCon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DanhMucController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);  // Sử dụng query 'page' hoặc mặc định là 1
        $numberRow = $request->query('number_row', 10);  // Sử dụng query 'number_row' hoặc mặc định là 9

        $data = DanhMuc::with('danhMucCons')->paginate($numberRow, ['*'], 'page', $page);

        return response()->json($data);
    }
    public function getAllDanhMuc()
    {
        $data = DanhMuc::with('danhMucCons')->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDanhMucRequest $request)
    {

        $data = DanhMuc::query()->create($request->all());

        return response()->json([
            'message' => 'Danh mục được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DanhMuc::query()->findOrFail($id);


            return response()->json([
                'message' => 'Chi tiết danh mục id = ' . $id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Không tìm thấy danh mục id = ' . $id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa danh mục: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy danh mục id = ' . $id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDanhMucRequest $request, string $id)
    {
        try {
            $data = DanhMuc::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật danh mục id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật danh mục: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function updateStatus(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'danh_muc_id' => 'required|integer|exists:danh_mucs,id', // Kiểm tra danh_muc_id tồn tại
            'trang_thai'    => 'required|boolean', // Giá trị trạng thái phải là 0 hoặc 1
        ]);

        // Tìm khách hàng theo ID
        $data = DanhMuc::find($validated['danh_muc_id']);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Phương thức vận chuyển không tìm thấy.',
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
                'danh_muc_id' => $data->id,
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
            DanhMuc::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa danh mục: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa danh mục',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
