<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_don_hang',
        'khach_hang_id',
        'phuong_thuc_thanh_toan_id',
        'hinh_thuc_van_chuyen_id',
        'trang_thai_don_hang_id',
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
    public function phuongThucThanhToan()
    {
        return $this->belongsTo(PhuongThucThanhToan::class, 'phuong_thuc_thanh_toan_id');
    }
    public function hinhThucVanChuyen()
    {
        return $this->belongsTo(HinhThucVanChuyen::class, 'hinh_thuc_van_chuyen_id');
    }
    public function trangThaiDonHang()
    {
        return $this->belongsTo(TrangThaiDonHang::class, 'trang_thai_don_hang_id');
    }
}
