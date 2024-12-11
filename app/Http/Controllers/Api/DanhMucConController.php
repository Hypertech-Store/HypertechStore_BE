<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhMucConRequest;
use App\Models\DanhMucCon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DanhMucConController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $danh_muc_id): \Illuminate\Http\JsonResponse
    {
        try {
            // Lấy danh sách danh mục con theo danh_muc_id
            $data = DanhMucCon::query()
                ->where('danh_muc_id', $danh_muc_id)
                ->get();

            return response()->json([
                'message' => 'Danh sách danh mục con thuộc danh mục id = ' . $danh_muc_id,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách danh mục con: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy danh sách danh mục con',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDanhMucConRequest $request)
    {

        $data = DanhMucCon::query()->create($request->all());

        return response()->json([
            'message' => 'Danh mục con được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Lấy thông tin danh mục con cùng với tên danh mục
            $data = DanhMucCon::with('danhMuc')->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết danh mục con id = ' . $id,
                'data' => [
                    'ten_danh_muc_con' => $data->ten_danh_muc_con,
                    'ten_danh_muc' => $data->danhMuc->ten_danh_muc ?? 'Danh mục không tồn tại',
                ]
            ]);
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục con id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết danh mục con: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi lấy chi tiết danh mục con',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDanhMucConRequest $request, string $id)
    {
        try {
            $data = DanhMucCon::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật danh mục con id = ' . $id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục con id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật danh mục con: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục con',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DanhMucCon::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy danh mục con id = ' . $id,
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa danh mục con: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa danh mục con',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display all resources.
     */
    public function getAll(): \Illuminate\Http\JsonResponse
    {
        $data = DanhMucCon::query()->get();

        return response()->json($data);
    }


}
