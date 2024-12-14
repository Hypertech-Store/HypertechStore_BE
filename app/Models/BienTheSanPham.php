<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'gia',
        'so_luong_kho'
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }
    public function chiTietGioHang()
    {
        return $this->hasMany(ChiTietGioHang::class, 'bien_the_san_pham_id', 'id');
    }
    public function giaTriThuocTinh()
    {
        return $this->belongsToMany(GiaTriThuocTinh::class, 'lien_ket_bien_the_va_gia_tri_thuoc_tinhs', 'bien_the_san_pham_id', 'gia_tri_thuoc_tinh_id');
    }

    public function lienKetBienTheVaGiaTri()
    {
        return $this->hasMany(LienKetBienTheVaGiaTriThuocTinh::class, 'bien_the_san_pham_id', 'id');
    }

    public function hinhAnhSanPhams()
    {
        return $this->hasMany(HinhAnhSanPham::class, 'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id', 'id');
    }
}
