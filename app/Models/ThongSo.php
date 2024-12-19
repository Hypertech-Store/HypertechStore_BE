<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongSo extends Model
{
    use HasFactory;
    protected $fillable = [
        'danh_muc_id',
        'ten_thong_so',
        'mo_ta',
    ];

    // Mối quan hệ với bảng danh_mucs
    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'danh_muc_id');
    }

    // Mối quan hệ với bảng san_pham_va_thong_sos
    public function sanPhams()
    {
        return $this->belongsToMany(SanPham::class, 'san_pham_va_thong_sos', 'thong_so_id', 'san_pham_id');
    }
    public function sanPhamVaThongSo()
{
    return $this->hasMany(SanPhamVaThongSo::class, 'thong_so_id');
}

}
