<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'khach_hang_id',
        'trang_thai_don_hang',
        'tong_tien',
        'dia_chi_giao_hang',
        'phuong_thuc_thanh_toan',
        'created_at',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'ma_khach_hang');
    }

    public function chiTietDonHangs()
    {
        return $this->hasMany(ChiTietDonHang::class, 'ma_don_hang');
    }

    public function giaoHangs()
    {
        return $this->hasMany(GiaoHang::class, 'ma_don_hang');
    }
}
