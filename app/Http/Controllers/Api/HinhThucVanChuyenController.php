<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HinhThucVanChuyen;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HinhThucVanChuyenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = HinhThucVanChuyen::query()->paginate(10);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ten_van_chuyen' => 'required|unique:hinh_thuc_van_chuyens,ten_van_chuyen|max:255',
            'gia_van_chuyen' => 'nullable|numeric',
            'mo_ta' => 'nullable|string',
        ]);
        $data = HinhThucVanChuyen::query()->create($request->all());

        return response()->json([
            'message' => 'Hình thức vận chuyển được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = HinhThucVanChuyen::query()->findOrFail($id);


            return response()->json([
                'message' => 'Chi tiết hình thức vận chuyển id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy hình thức vận chuyển id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa hình thức vận chuyển: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy hình thức vận chuyển id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'ten_van_chuyen' => 'required|max:255|unique:hinh_thuc_van_chuyens,ten_van_chuyen,' . $id,
                'gia_van_chuyen' => 'nullable|numeric',
                'mo_ta' => 'nullable|string',
            ]);

            $data = HinhThucVanChuyen::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật hình thức vận chuyển id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình thức vận chuyển id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật hình thức vận chuyển: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật hình thức vận chuyển',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            $data = HinhThucVanChuyen::findOrFail($id);

            $data->delete();

            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy hình thức vận chuyển id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa hình thức vận chuyển: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa hình thức vận chuyển',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
