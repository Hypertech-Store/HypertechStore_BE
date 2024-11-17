<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuanTriVien extends Model
{
    use HasFactory;
    protected $fillable = [
        'ten_dang_nhap',
        'mat_khau',
        'ho_ten',
        'email',
        'role',
        'trang_thai'
    ];
}
