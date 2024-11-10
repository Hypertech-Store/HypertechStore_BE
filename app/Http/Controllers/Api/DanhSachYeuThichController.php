<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhSachYeuThichRequest;
use App\Models\DanhSachYeuThich;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DanhSachYeuThichController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DanhSachYeuThich::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDanhSachYeuThichRequest $request)
    {

        $data = DanhSachYeuThich::query()->create($request->all());

        return response()->json([
            'message' => 'Đã thêm vào danh sách yêu thích',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DanhSachYeuThich::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết danh sách yêu thích id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy danh sách yêu thích id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa danh sách yêu thích: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy danh sách yêu thích id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDanhSachYeuThichRequest $request, string $id)
    {
        try {
            $data = DanhSachYeuThich::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật danh sách yêu thích id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh sách yêu thích id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật danh sách yêu thích: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật danh sách yêu thích',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DanhSachYeuThich::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh sách yêu thích id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa danh sách yêu thích: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa danh sách yêu thích',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function danhSachYeuThich($khachHangId)
{
    $data = DanhSachYeuThich::query()
        ->where('khach_hang_id', $khachHangId)
        ->get();

    return response()->json($data);
}
}
