<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    use HasFactory;

    protected $fillable = [
        'ten_nguoi_dung',
        'ho_ten',
        'email',
        'dien_thoai',
        'dia_chi',
        'gioi_tinh',
        'ngay_sinh',
        'mat_khau',
        'mat_khau_reset_token',
    ];

    public function donHangs()
    {
        return $this->hasMany(DonHang::class);
    }

    public function danhSachYeuThichs()
    {
        return $this->hasMany(DanhSachYeuThich::class);
    }

    public function binhLuans()
    {
        return $this->hasMany(BinhLuan::class);
    }
}
