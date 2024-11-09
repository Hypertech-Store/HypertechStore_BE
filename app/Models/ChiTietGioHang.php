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
        'so_luong',
        'gia', // Thêm trường này để có thể truyền giá trị khi tạo bản ghi
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class);
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }
}
