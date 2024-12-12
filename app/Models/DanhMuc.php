<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    use HasFactory;

    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
    ];

    public function danhMucCons()
    {
        return $this->hasMany(DanhMucCon::class);
    }

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class);
    }
    public function thongSos()
    {
        return $this->hasMany(ThongSo::class, 'danh_muc_id');
    }
}
