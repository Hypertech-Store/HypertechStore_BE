<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HinhAnhSanPham extends Model
{
    use HasFactory;

    protected $fillable = [
        'san_pham_id',
        'duong_dan_anh',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class);
    }
}
