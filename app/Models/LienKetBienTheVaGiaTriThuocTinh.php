<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LienKetBienTheVaGiaTriThuocTinh extends Model
{
    use HasFactory;
    protected $fillable = [
        'bien_the_san_pham_id',
        'gia_tri_thuoc_tinh_id',
        'anh_bien_the'
    ];

    // Quan hệ n-1 với bảng GiaTriThuocTinh
    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class);
    }

    public function giaTriThuocTinh()
    {
        return $this->belongsTo(GiaTriThuocTinh::class);
    }
}
