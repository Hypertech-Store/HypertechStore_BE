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
        'ram',
        'dung_luong_luu_tru',
        'camera_sau',
        'camera_truoc',
        'cong_nghe_man_hinh',
        'dung_luong_pin',
        'loai_pin',
        'khoi_luong',
        'thoi_diem_ra_mat',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
