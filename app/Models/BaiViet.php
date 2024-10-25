<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaiViet extends Model
{
    use HasFactory;
    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'tac_gia',
-       'trang_thai',
        'hinh_anh',
        'so_luot_xem',
        'danh_gia',
        'so_binh_luan',
        'tag',
        'loai_bai_viet',
    ];
}
