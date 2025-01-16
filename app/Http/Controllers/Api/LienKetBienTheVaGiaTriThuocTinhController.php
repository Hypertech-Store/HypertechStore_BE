<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLienKetBienTheVaGiaTriThuocTinhRequest;
use App\Models\LienKetBienTheVaGiaTriThuocTinh;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LienKetBienTheVaGiaTriThuocTinhController extends Controller
{
    public function index()
    {
        $data = LienKetBienTheVaGiaTriThuocTinh::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLienKetBienTheVaGiaTriThuocTinhRequest $request)
    {

        $data = LienKetBienTheVaGiaTriThuocTinh::query()->create($request->all());

        return response()->json([
            'message' => 'Liên kết biến thể và giá trị thuộc tính được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = LienKetBienTheVaGiaTriThuocTinh::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết liên kết biến thể và giá trị thuộc tính id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy liên kết biến thể và giá trị thuộc tính id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa liên kết biến thể và giá trị thuộc tính: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy liên kết biến thể và giá trị thuộc tính id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreLienKetBienTheVaGiaTriThuocTinhRequest $request, string $id)
    {
        try {
            $data = LienKetBienTheVaGiaTriThuocTinh::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật liên kết biến thể và giá trị thuộc tính id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy liên kết biến thể và giá trị thuộc tính id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật liên kết biến thể và giá trị thuộc tính: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật liên kết biến thể và giá trị thuộc tính',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            LienKetBienTheVaGiaTriThuocTinh::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy liên kết biến thể và giá trị thuộc tính id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa liên kết biến thể và giá trị thuộc tính: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa liên kết biến thể và giá trị thuộc tính',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
