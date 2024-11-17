<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongSoDienThoai extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'he_dieu_hanh',
        'chip_xu_ly',
        'toc_do_cpu',
        'chip_do_hoa',
        'ram',
        'dung_luong_luu_tru',
        'dung_luong_con_lai',
        'the_nho',
        'danh_ba',
        'camera_sau_resolution',
        'camera_sau_video',
        'camera_sau_flash',
        'camera_sau_tinh_nang',
        'camera_truoc_resolution',
        'camera_truoc_tinh_nang',
        'cong_nghe_man_hinh',
        'man_hinh_resolution',
        'man_hinh_rong',
        'man_hinh_do_sang_max',
        'mat_kinh_cam_ung',
        'dung_luong_pin',
        'loai_pin',
        'sac_toi_da',
        'sac_kem_theo',
        'cong_nghe_pin',
        'bao_mat_nang_cao',
        'tinh_nang_dac_biet',
        'khang_nuoc_bui',
        'ghi_am',
        'radio',
        'xem_phim',
        'nghe_nhac',
        'mang_di_dong',
        'sim',
        'wifi',
        'gps',
        'bluetooth',
        'cong_ket_noi_sac',
        'jack_tai_nghe',
        'ket_noi_khac',
        'thiet_ke',
        'chat_lieu',
        'kich_thuoc_khoi_luong',
        'thoi_diem_ra_mat',
        'hang',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
