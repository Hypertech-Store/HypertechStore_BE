<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HinhAnhSanPham extends Model
{
    use HasFactory;

    protected $fillable = [
        'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id',
        'duong_dan_hinh_anh',
    ];

    public function lienKetBienTheVaThuocTinh()
    {
        return $this->belongsTo(LienKetBienTheVaGiaTriThuocTinh::class, 'lien_ket_bien_the_va_gia_tri_thuoc_tinh_id', 'id');
    }
}
