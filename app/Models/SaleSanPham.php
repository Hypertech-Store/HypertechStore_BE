<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleSanPham extends Model
{
    use HasFactory;

    protected $table = 'sale_san_phams'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'san_pham_id',
        'sale_theo_phan_tram',
        'ngay_bat_dau_sale',
        'ngay_ket_thuc_sale',
    ];

    /**
     * Quan hệ với model Product (một PriceSale thuộc về một Product)
     */
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }

    /**
     * Tính giá sau giảm dựa trên phần trăm giảm giá và giá gốc của sản phẩm
     */
    public function calculateSalePrice($gia)
    {
        return ($gia * $this->sale_theo_phan_tram) / 100;
    }
}
