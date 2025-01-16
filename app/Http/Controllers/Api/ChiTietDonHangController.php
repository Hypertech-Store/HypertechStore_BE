<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChiTietDonHangRequest;
use App\Models\ChiTietDonHang;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ChiTietDonHangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ChiTietDonHang::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChiTietDonHangRequest $request)
    {

        $data = ChiTietDonHang::query()->create($request->all());

        return response()->json([
            'message' => 'Chi tiết đơn hàng được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = ChiTietDonHang::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết chi tiết đơn hàng id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy chi tiết đơn hàng id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa chi tiết đơn hàng: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy chi tiết đơn hàng id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreChiTietDonHangRequest $request, string $id)
    {
        try {
            $data = ChiTietDonHang::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật chi tiết đơn hàng id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy chi tiết đơn hàng id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật chi tiết đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật chi tiết đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            ChiTietDonHang::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy chi tiết đơn hàng id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa chi tiết đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa chi tiết đơn hàng',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
