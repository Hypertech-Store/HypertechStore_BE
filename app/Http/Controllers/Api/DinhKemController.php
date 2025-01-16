<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDinhKemRequest;
use App\Models\DinhKem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DinhKemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DinhKem::query()->get();

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDinhKemRequest $request)
    {

        $data = DinhKem::query()->create($request->all());

        return response()->json([
            'message' => 'Đính kèm được tạo thành công!',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = DinhKem::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết đính kèm id = '.$id,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            if($th instanceof ModelNotFoundException){
                return response()->json([
                    'message' => 'Không tìm thấy đính kèm id = '.$id,

                ], Response::HTTP_NOT_FOUND);
            }
            Log::error('Lỗi xóa đính kèm: ' . $th->getMessage());

            return response()->json([
                'message' => 'Không tìm thấy đính kèm id = '.$id,

            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDinhKemRequest $request, string $id)
    {
        try {
            $data = DinhKem::query()->findOrFail($id);
            $data->update($request->all());

            return response()->json([
                'message' => 'Cập nhật đính kèm id = '.$id,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đính kèm id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật đính kèm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật đính kèm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            DinhKem::destroy($id);
            return response()->json([
                'message' => 'Xóa thành công',
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy đính kèm id = '.$id,
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            Log::error('Lỗi xóa đính kèm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa đính kèm',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
