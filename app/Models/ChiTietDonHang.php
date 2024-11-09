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
        'so_luong',
        'gia',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'ma_don_hang');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'ma_san_pham');
    }
}
