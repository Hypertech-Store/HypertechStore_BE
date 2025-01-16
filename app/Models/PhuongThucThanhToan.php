<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhuongThucThanhToan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'ten_phuong_thuc',
        'anh_phuong_thuc',
        'trang_thai'
    ];

    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'phuong_thuc_thanh_toan_id');
    }
}
