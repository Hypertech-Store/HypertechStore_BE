<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrangThaiGiaoHang extends Model
{
    use HasFactory;
    protected $fillable = [
        'ten_trang_thai',
        'mo_ta',
    ];

    public function giaoHangs()
    {
        return $this->hasMany(GiaoHang::class, 'trang_thai_giao_hang_id');
    }
}
