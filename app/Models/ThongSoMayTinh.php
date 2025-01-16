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
        'so_nhan',
        'so_luong_luong',
        'toc_do_cpu',
        'toc_do_toi_da',
        'bo_nho_cache',
        'ram',
        'loai_ram',
        'toc_do_bus_ram',
        'ho_tro_ram_toi_da',
        'o_cung',
        'man_hinh',
        'do_phan_giai',
        'tan_so_quet',
        'cong_nghe_man_hinh',
        'card_do_hoa',
        'cong_nghe_am_thanh',
        'cong_giao_tiep',
        'ket_noi_khong_day',
        'webcam',
        'tinh_nang_khac',
        'den_ban_phim',
        'khoi_luong',
        'thoi_diem_ra_mat',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
