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
        'ho_ten',
        'email',
        'dien_thoai',
        'dia_chi',
        'mat_khau',
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
