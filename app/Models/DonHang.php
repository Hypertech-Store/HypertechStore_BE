<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'khach_hang_id',
        'phuong_thuc_thanh_toan_id',
        'trang_thai_don_hang',
        'tong_tien',
        'dia_chi_giao_hang',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class);
    }

    public function chiTietDonHangs()
    {
        return $this->hasMany(ChiTietDonHang::class);
    }

    public function giaoHangs()
    {
        return $this->hasMany(GiaoHang::class);
    }
}
