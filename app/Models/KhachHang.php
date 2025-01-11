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
        'hinh_anh',
        'trang_thai',
        'mat_khau',
        'mat_khau_reset_token',
    ];

    // Mặc định status = 0 (không hoạt động)
    protected $attributes = [
        'trang_thai' => 0,
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
