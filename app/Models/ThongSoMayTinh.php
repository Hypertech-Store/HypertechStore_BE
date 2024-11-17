<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongSoMayTinh extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'cong_nghe_cpu',
        'ram',
        'o_cung',
        'man_hinh',
        'tan_so_quet',
        'card_man_hinh',
        'cong_nghe_am_thanh',
        'he_dieu_hanh',
        'khoi_luong',
        'thoi_diem_ra_mat',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
