<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;

    protected $fillable = [
        'don_hang_id',
        'phuong_thuc_thanh_toan_id',
        'so_tien_thanh_toan',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class);
    }

    public function phuongThucThanhToan()
    {
        return $this->belongsTo(PhuongThucThanhToan::class);
    }

}
