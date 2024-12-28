<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrangThaiDonHang extends Model
{
    use HasFactory;
    protected $fillable = [
        'ten_trang_thai',
        'mo_ta',
    ];
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'trang_thai_don_hang_id');
    }

}
