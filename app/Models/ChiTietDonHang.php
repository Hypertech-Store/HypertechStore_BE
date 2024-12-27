<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'don_hang_id',
        'san_pham_id',
        'bien_the_san_pham_id',
        'thuoc_tinh',
        'so_luong',
        'gia',
    ];

    // Mối quan hệ với bảng DonHang
    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id', 'id');
    }

    // Mối quan hệ với bảng BienTheSanPham
    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'bien_the_san_pham_id', 'id');
    }

    // Mối quan hệ với bảng SanPham
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id', 'id');
    }

    // Mối quan hệ thông qua với bảng ThuocTinh
    public function thuocTinh()
    {
        return $this->hasManyThrough(
            ThuocTinhSanPham::class,
            BienTheSanPham::class,
            'id', // Foreign key trên bảng BienTheSanPham
            'id', // Foreign key trên bảng ThuocTinh
            'bien_the_san_pham_id', // Local key trên bảng ChiTietDonHang
            'thuoc_tinh_id' // Local key trên bảng BienTheSanPham
        );
    }

    // Mối quan hệ với bảng HinhAnh (nếu muốn lấy ảnh của sản phẩm)
    public function hinhAnhSanPham()
    {
        return $this->hasManyThrough(
            HinhAnhSanPham::class,
            BienTheSanPham::class,
            'id', // Foreign key trên bảng BienTheSanPham
            'bien_the_san_pham_id', // Foreign key trên bảng HinhAnhSanPham
            'bien_the_san_pham_id', // Local key trên bảng ChiTietDonHang
            'id' // Local key trên bảng BienTheSanPham
        );
    }
}
