<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuGiamGia extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_giam_gia',
        'mo_ta',
        'loai_giam_gia',
        'gia_tri_giam_gia',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'gia_tri_don_hang_toi_thieu',
        'so_luong_san_pham_toi_thieu',
        'so_luot_su_dung',
    ];

    public function phieuGiamGiaVaKhachHangs()
    {
        return $this->hasMany(PhieuGiamGiaVaKhachHang::class);
    }
}
