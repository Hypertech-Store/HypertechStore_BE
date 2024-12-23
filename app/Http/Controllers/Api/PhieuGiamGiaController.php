<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePhieuGiamGiaRequest;
use App\Models\PhieuGiamGia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhieuGiamGiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhieuGiamGiaRequest $request)
    {
        // Lấy dữ liệu đã được validate từ Request
        $validated = $request->validated();

        // Tự động sinh mã giảm giá
        $validated['ma_giam_gia'] = strtoupper(Str::random(10)); // Sinh chuỗi ngẫu nhiên gồm 10 ký tự

        // Tạo phiếu giảm giá
        $phieuGiamGia = PhieuGiamGia::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Phiếu giảm giá đã được tạo thành công.',
            'data' => $phieuGiamGia,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
