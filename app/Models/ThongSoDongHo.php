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
        'dung_luong_pin',
        'thoi_gian_su_dung',
        'cpu',
        'he_dieu_hanh',
        'cam_bien',
        'khoi_luong',
        'thoi_diem_ra_mat',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
