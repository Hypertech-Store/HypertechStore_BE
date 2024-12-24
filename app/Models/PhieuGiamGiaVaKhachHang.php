<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuGiamGiaVaKhachHang extends Model
{
    use HasFactory;
    protected $fillable = [
        'phieu_giam_gia_id',
        'khach_hang_id',
        'don_hang_id',
        'hinh_thuc_van_chuyen_id'
    ];

    public function phieuGiamGia()
    {
        return $this->belongsTo(PhieuGiamGia::class);
    }

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class);
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class);
    }

}
