<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDanhGia extends Model
{
    use HasFactory;
    protected $fillable = [
        'danh_gia_id',
        'hinh_anh_duong_dan',
    ];

    // Quan hệ với DanhGia
    public function danhGia()
    {
        return $this->belongsTo(DanhGia::class, 'danh_gia_id');
    }
}
