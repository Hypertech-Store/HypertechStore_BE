<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThuocTinhSanPham extends Model
{
    use HasFactory;
    protected $fillable = [
        'ten_thuoc_tinh',
    ];

    // Quan hệ 1-n với bảng GiaTriThuocTinh
    public function giaTriThuocTinh()
    {
        return $this->hasMany(GiaTriThuocTinh::class);
    }
}
