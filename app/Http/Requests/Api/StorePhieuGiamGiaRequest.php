<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StorePhieuGiamGiaRequest extends FormRequest
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
            'ma_giam_gia' => 'nullable|string|max:50|unique:phieu_giam_gias,ma_giam_gia',
            'mo_ta' => 'nullable|string',
            'loai_giam_gia' => 'required|in:theo phần trăm,theo số tiền nhất định',
            'gia_tri_giam_gia' => 'required|numeric|min:0',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'gia_tri_don_hang_toi_thieu' => 'nullable|numeric|min:0',
            'so_luong_san_pham_toi_thieu' => 'nullable|integer|min:1',
            'so_luot_su_dung' => 'nullable|integer|min:0',
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
