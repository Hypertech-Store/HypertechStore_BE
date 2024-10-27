<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Dtos\AllProductResponseDTO;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SanPhamController extends Controller
{
    public function getAllProduct(Request $request): JsonResponse
    {
        // Lấy query param với giá trị mặc định: page = 1 và number_row = 10
        $page = $request->query('page', 1);
        $numberRow = $request->query('number_row', 10);

        // Lấy dữ liệu với phân trang
        $sanPhams = SanPham::paginate($numberRow, ['*'], 'page', $page);

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($sanPhams);
    }
}
