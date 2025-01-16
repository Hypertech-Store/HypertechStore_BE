<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreThongSoRequest extends FormRequest
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
    public function rules()
    {
        return [
            // Kiểm tra thong_so_list là mảng
            'thong_so_list' => 'required|array',
            'thong_so_list.*.danh_muc_id' => 'required|exists:danh_mucs,id', // Kiểm tra danh_muc_id có tồn tại trong bảng categories
            'thong_so_list.*.ten_thong_so' => 'required|string|max:255', // Kiểm tra ten_thong_so không rỗng và là chuỗi
            'thong_so_list.*.mo_ta' => 'nullable|string', // Mô tả có thể trống
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
