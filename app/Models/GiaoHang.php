<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaoHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'don_hang_id',
        'trang_thai_giao_hang_id',

        'phuong_thuc_giao_hang',
        'phi_giao_hang',
        'ngay_giao_hang',
        'ngay_nhan_hang',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'ma_don_hang', 'ma_don_hang');
    }

    public function trangThaiGiaoHang()
    {
        return $this->belongsTo(TrangThaiGiaoHang::class, 'ma_trang_thai_giao_hang', 'ma_trang_thai_giao_hang');
    }
}
