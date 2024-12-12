<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPhamVaThongSo extends Model
{
    use HasFactory;
    protected $fillable = [
        'san_pham_id',
        'thong_so_id',
    ];

    // Mối quan hệ với bảng san_phams
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    // Mối quan hệ với bảng thong_sos
    public function thongSo()
    {
        return $this->belongsTo(ThongSo::class, 'thong_so_id');
    }
}
