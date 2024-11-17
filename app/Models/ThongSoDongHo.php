<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongSoDongHo extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'cong_nghe_man_hinh',
        'kich_thuoc_man_hinh',
        'do_phan_giai',
        'kich_thuoc_mat',
        'chat_lieu_mat',
        'chat_lieu_khung_vien',
        'chat_lieu_day',
        'do_rong_day',
        'do_dai_day',
        'kha_nang_thay_day',
        'mon_the_thao',
        'ho_tro_ngoai_ghi',
        'tien_ich_dac_biet',
        'chong_nuoc',
        'theo_doi_suc_khoe',
        'tien_ich_khac',
        'hien_thi_thong_bao',
        'thoi_gian_su_dung_pin',
        'thoi_gian_sac',
        'dung_luong_pin',
        'cong_sac',
        'cpu',
        'bo_nho_trong',
        'he_dieu_hanh',
        'ket_noi_he_dieu_hanh',
        'ung_dung_quan_ly',
        'ket_noi',
        'cam_bien',
        'dinh_vi',
        'san_xuat_tai',
        'thoi_diem_ra_mat',
        'ngon_ngu',
        'hang_san_xuat',
    ];


    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
