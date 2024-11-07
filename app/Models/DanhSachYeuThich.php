<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhSachYeuThich extends Model
{
    use HasFactory;
    protected $table = 'danh_sach_yeu_thichs';
    protected $fillable = [
        'khach_hang_id',
        'san_pham_id',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'ma_khach_hang');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'ma_san_pham');
    }
}
