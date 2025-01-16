<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreChiTietDonHangRequest extends FormRequest
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
            'don_hang_id' => 'required|integer|exists:don_hangs,id',
            'san_pham_id' => 'required|integer|exists:san_phams,id',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0',
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
