<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietGioHang extends Model
{
    use HasFactory;

    protected $fillable = [
        'gio_hang_id',
        'san_pham_id',
        'bien_the_san_pham_id',
        'so_luong',
        'gia',
        'thuoc_tinh'
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class);
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }
    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'bien_the_san_pham_id', 'id');
    }
}

