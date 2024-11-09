<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'ten_bien_the',
        'gia_tri_bien_the',
        'gia',
        'so_luong_kho'
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }
}
