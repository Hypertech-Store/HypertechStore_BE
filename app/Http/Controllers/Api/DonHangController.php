<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonHangRequest;
use App\Models\DonHang;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DonHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($khachHangId)
    {
        try {
            $data = DonHang::query()->where('khach_hang_id', $khachHangId)->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng cho khách hàng này.'
                ], 404);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình truy vấn.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDonHangRequest $request)
    {

        $data = DonHang::query()->create($request->all());

        return response()->json([
            'message' => 'Đơn hàng được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DonHang::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết đơn hàng id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa đơn hàng: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = DonHang::query()->findOrFail($id);

            $updatedData = [];
            if ($request->has('trang_thai_don_hang')) {
                $updatedData['trang_thai_don_hang'] = $request->input('trang_thai_don_hang');
            }

            if (!empty($updatedData)) {
                $data->update($updatedData);
            }


            return response()->json([
                'message' => 'Cập nhật trạng thái đơn hàng id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DonHang::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
