<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDanhMucConRequest;
use App\Models\DanhMucCon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DanhMucConController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // Lấy số trang và số lượng bản ghi mỗi trang từ query string (có giá trị mặc định)
        $page = $request->query('page', 1);  // Sử dụng query 'page' hoặc mặc định là 1
        $numberRow = $request->query('number_row', 9);  // Sử dụng query 'number_row' hoặc mặc định là 9

        // Phân trang danh mục con
        $data = DanhMucCon::with('danhMuc')->paginate($numberRow, ['*'], 'page', $page);

        // Trả về dữ liệu dạng JSON
        return response()->json($data);
    }
    public function getSubCategoriesByCategoryId(string $danh_muc_id): \Illuminate\Http\JsonResponse
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
        $data = $request->all();

        if ($request->hasFile('image')) {
            // Upload hình ảnh và lưu đường dẫn
            $path = $request->file('image')->store('danh_muc_cons', 'public');
            Log::info('Đường dẫn hình ảnh:', ['path' => $path]);
            $data['img'] = $path;
            unset($data['image']);
        }
        DanhMucCon::query()->create($data);
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
                'data' => $data
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
    public function update(Request $request, string $id)
    {
        try {
            $data = DanhMucCon::query()->findOrFail($id);

            if ($request->hasFile('image')) {
                if ($data->img && Storage::exists('public/' . $data->img)) {
                    Storage::delete('public/' . $data->img);
                }
                $path =  $request->file('image')->store('danh_muc_cons', 'public');
                Log::info('Đường dẫn hình ảnh mới:', ['path' => $path]);
                $data->update([
                    'img' => $path,
                ]);
            }
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
