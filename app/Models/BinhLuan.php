<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinhLuan extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'khach_hang_id',
        'noi_dung',

    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class);
    }
}
