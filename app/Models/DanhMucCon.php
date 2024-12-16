<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMucCon extends Model
{
    use HasFactory;

    protected $fillable = [
        'danh_muc_id',
        'ten_danh_muc_con',
        'img'

    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class);
    }

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class);
    }
}
