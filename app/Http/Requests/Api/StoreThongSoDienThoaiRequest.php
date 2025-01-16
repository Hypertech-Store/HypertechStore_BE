<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreThongSoDienThoaiRequest extends FormRequest
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
            // 'he_dieu_hanh' => 'nullable|string|max:255',
            // 'chip_xu_ly' => 'nullable|string|max:255',
            // 'toc_do_cpu' => 'nullable|numeric',
            // 'chip_do_hoa' => 'nullable|string|max:255',
            // 'ram' => 'nullable|numeric|min:1',
            // 'dung_luong_luu_tru' => 'nullable|numeric|min:1',
            // 'dung_luong_con_lai' => 'nullable|numeric',
            // 'the_nho' => 'nullable|string|max:255',
            // 'danh_ba' => 'nullable|boolean',
            // 'camera_sau_resolution' => 'nullable|string|max:255',
            // 'camera_sau_video' => 'nullable|string|max:255',
            // 'camera_sau_flash' => 'nullable|string|max:255',
            // 'camera_sau_tinh_nang' => 'nullable|string|max:255',
            // 'camera_truoc_resolution' => 'nullable|string|max:255',
            // 'camera_truoc_tinh_nang' => 'nullable|string|max:255',
            // 'cong_nghe_man_hinh' => 'nullable|string|max:255',
            // 'man_hinh_resolution' => 'nullable|string|max:255',
            // 'man_hinh_rong' => 'nullable|numeric',
            // 'man_hinh_do_sang_max' => 'nullable|numeric',
            // 'mat_kinh_cam_ung' => 'nullable|string|max:255',
            // 'dung_luong_pin' => 'nullable|numeric',
            // 'loai_pin' => 'nullable|string|max:255',
            // 'sac_toi_da' => 'nullable|numeric',
            // 'sac_kem_theo' => 'nullable|boolean',
            // 'cong_nghe_pin' => 'nullable|string|max:255',
            // 'bao_mat_nang_cao' => 'nullable|boolean',
            // 'tinh_nang_dac_biet' => 'nullable|string|max:255',
            // 'khang_nuoc_bui' => 'nullable|boolean',
            // 'ghi_am' => 'nullable|boolean',
            // 'radio' => 'nullable|boolean',
            // 'xem_phim' => 'nullable|boolean',
            // 'nghe_nhac' => 'nullable|boolean',
            // 'mang_di_dong' => 'nullable|boolean',
            // 'sim' => 'nullable|string|max:255',
            // 'wifi' => 'nullable|boolean',
            // 'gps' => 'nullable|boolean',
            // 'bluetooth' => 'nullable|boolean',
            // 'cong_ket_noi_sac' => 'nullable|string|max:255',
            // 'jack_tai_nghe' => 'nullable|string|max:255',
            // 'ket_noi_khac' => 'nullable|string|max:255',
            // 'thiet_ke' => 'nullable|string|max:255',
            // 'chat_lieu' => 'nullable|string|max:255',
            // 'kich_thuoc_khoi_luong' => 'nullable|string|max:255',
            // 'thoi_diem_ra_mat' => 'nullable|string|max:255',
            // 'hang' => 'nullable|string|max:255',

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
