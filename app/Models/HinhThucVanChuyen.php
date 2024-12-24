<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HinhThucVanChuyen extends Model
{
    use HasFactory;

    protected $fillable = [
        'ten_van_chuyen',
        'mo_ta',
        'gia_van_chuyen'
    ];
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'hinh_thuc_van_chuyen_id');
    }

}
