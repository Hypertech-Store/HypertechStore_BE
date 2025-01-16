<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'khach_hang_id',
        'trang_thai',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class);
    }

    public function chiTietGioHangs()
    {
        return $this->hasMany(ChiTietGioHang::class);
    }
}
