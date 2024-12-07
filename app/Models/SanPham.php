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
    public function thongSoKyThuat()
    {
        // Kiểm tra danh mục của sản phẩm và trả về thông số kỹ thuật tương ứng
        switch ($this->danhMuc->ten_danh_muc) {
            case 'Điện thoại':
                return $this->hasOne(ThongSoDienThoai::class);  // Nếu là điện thoại, trả về thông số kỹ thuật từ bảng `phones`
            case 'Đồng Hồ':
                return $this->hasOne(ThongSoDongHo::class);  // Nếu là đồng hồ, trả về thông số kỹ thuật từ bảng `watches`
            case 'Máy tính':
                return $this->hasOne(ThongSoMayTinh::class);  // Nếu là máy tính, trả về thông số kỹ thuật từ bảng `computers`
            default:
                return null;  // Nếu không phải sản phẩm thuộc danh mục trên, không có thông số kỹ thuật
        }
    }
    public function thongSoDongHo()
    {
        return $this->hasOne(ThongSoDongHo::class, 'san_pham_id');
    }

    public function thongSoMayTinh()
    {
        return $this->hasOne(ThongSoMayTinh::class, 'san_pham_id');
    }

    public function thongSoDienThoai()
    {
        return $this->hasOne(ThongSoDienThoai::class, 'san_pham_id');
    }


}
