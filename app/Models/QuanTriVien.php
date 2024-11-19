<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuanTriVien extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    use HasFactory;
    protected $fillable = [
        'ten_dang_nhap',
        'mat_khau',
        'ho_ten',
        'email',
        'role',
        'trang_thai',
        'anh_nguoi_dung',
        'dia_chi',
        'so_dien_thoai'
    ];
}
