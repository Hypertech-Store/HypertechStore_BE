<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    use HasFactory;

    protected $fillable = [
        'danh_muc_id',
        'danh_muc_con_id',
        'ten_san_pham',
        'mo_ta',
        'gia',
        'so_luong_ton_kho',
        'duong_dan_anh',
        'luot_xem'
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class);
    }

    public function danhMucCon()
    {
        return $this->belongsTo(DanhMucCon::class);
    }

    public function bienTheSanPhams()
    {
        return $this->hasMany(BienTheSanPham::class);
    }
    public function thuocTinhSanPhams()
    {
        return $this->belongsToMany(ThuocTinhSanPham::class, 'lien_ket_bien_the_va_gia_tri_thuoc_tinhs', 'san_pham_id', 'thuoc_tinh_san_pham_id');
    }

    // Quan hệ 1-n với bảng HinhAnhSanPham
    public function hinhAnhSanPhams()
    {
        return $this->hasMany(HinhAnhSanPham::class, 'san_pham_id');
    }
    public function danhGias()
    {
        return $this->hasMany(DanhGia::class);
    }

    public function binhLuans()
    {
        return $this->hasMany(BinhLuan::class);
    }

    public function chiTietDonHangs()
    {
        return $this->hasMany(ChiTietDonHang::class);
    }

    public function chiTietGioHangs()
    {
        return $this->hasMany(ChiTietGioHang::class);
    }
    public function saleSanPhams()
    {
        return $this->hasMany(SaleSanPham::class);
    }

    public function thongSos()
    {
        return $this->belongsToMany(ThongSo::class, 'san_pham_va_thong_sos', 'san_pham_id', 'thong_so_id');
    }


}
