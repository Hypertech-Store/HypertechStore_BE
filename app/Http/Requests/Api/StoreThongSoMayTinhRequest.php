<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreThongSoMayTinhRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'cong_nghe_cpu' => 'nullable|string',
            // 'so_nhan' => 'nullable|integer',
            // 'so_luong_luong' => 'nullable|integer',
            // 'toc_do_cpu' => 'nullable|string',
            // 'toc_do_toi_da' => 'nullable|string',
            // 'bo_nho_cache' => 'nullable|string',
            // 'ram' => 'nullable|integer',
            // 'loai_ram' => 'nullable|string',
            // 'toc_do_bus_ram' => 'nullable|string',
            // 'ho_tro_ram_toi_da' => 'nullable|string',
            // 'o_cung' => 'nullable|string',
            // 'man_hinh' => 'nullable|string',
            // 'do_phan_giai' => 'nullable|string',
            // 'tan_so_quet' => 'nullable|string',
            // 'cong_nghe_man_hinh' => 'nullable|string',
            // 'card_do_hoa' => 'nullable|string',
            // 'cong_nghe_am_thanh' => 'nullable|string',
            // 'cong_giao_tiep' => 'nullable|string',
            // 'ket_noi_khong_day' => 'nullable|string',
            // 'webcam' => 'nullable|string',
            // 'tinh_nang_khac' => 'nullable|string',
            // 'den_ban_phim' => 'nullable|string',
            // 'khoi_luong' => 'nullable|string',
            // 'thoi_diem_ra_mat' => 'nullable|string',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'errors' => $errors->messages(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

        throw new HttpResponseException($response);
    }
}
