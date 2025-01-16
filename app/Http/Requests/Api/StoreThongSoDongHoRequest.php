<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreThongSoDongHoRequest extends FormRequest
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
            // 'cong_nghe_man_hinh' => 'nullable|string|max:255',
            // 'kich_thuoc_man_hinh' => 'nullable|string|max:255',
            // 'do_phan_giai' => 'nullable|string|max:255',
            // 'kich_thuoc_mat' => 'nullable|string|max:255',
            // 'chat_lieu_mat' => 'nullable|string|max:255',
            // 'chat_lieu_khung_vien' => 'nullable|string|max:255',
            // 'chat_lieu_day' => 'nullable|string|max:255',
            // 'do_rong_day' => 'nullable|numeric',
            // 'do_dai_day' => 'nullable|numeric',
            // 'kha_nang_thay_day' => 'nullable|string|max:255',
            // 'mon_the_thao' => 'nullable|string|max:255',
            // 'ho_tro_ngoai_ghi' => 'nullable|string|max:255',
            // 'tien_ich_dac_biet' => 'nullable|string|max:255',
            // 'chong_nuoc' => 'nullable|boolean',
            // 'theo_doi_suc_khoe' => 'nullable|boolean',
            // 'tien_ich_khac' => 'nullable|string|max:255',
            // 'hien_thi_thong_bao' => 'nullable|boolean',
            // 'thoi_gian_su_dung_pin' => 'nullable|numeric',
            // 'thoi_gian_sac' => 'nullable|numeric',
            // 'dung_luong_pin' => 'nullable|numeric',
            // 'cong_sac' => 'nullable|string|max:255',
            // 'cpu' => 'nullable|string|max:255',
            // 'bo_nho_trong' => 'nullable|string|max:255',
            // 'he_dieu_hanh' => 'nullable|string|max:255',
            // 'ket_noi_he_dieu_hanh' => 'nullable|string|max:255',
            // 'ung_dung_quan_ly' => 'nullable|string|max:255',
            // 'ket_noi' => 'nullable|string|max:255',
            // 'cam_bien' => 'nullable|string|max:255',
            // 'dinh_vi' => 'nullable|string|max:255',
            // 'san_xuat_tai' => 'nullable|string|max:255',
            // 'thoi_diem_ra_mat' => 'nullable|string|max:255',
            // 'ngon_ngu' => 'nullable|string|max:255',
            // 'hang_san_xuat' => 'nullable|string|max:255',

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
