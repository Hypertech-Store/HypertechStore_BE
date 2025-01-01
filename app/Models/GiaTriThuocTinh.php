<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaTriThuocTinh extends Model
{
    use HasFactory;
    protected $fillable = [
        'thuoc_tinh_san_pham_id',
        'ten_gia_tri',
    ];

    // Quan hệ n-1 với bảng ThuocTinhSanPham
    public function thuocTinhSanPham()
    {
        return $this->belongsTo(ThuocTinhSanPham::class, 'thuoc_tinh_san_pham_id'); // Kiểm tra lại tên trường khóa ngoại
    }
    public function lienKetBienTheVaGiaTri()
    {
        return $this->hasMany(LienKetBienTheVaGiaTriThuocTinh::class);
    }
}
