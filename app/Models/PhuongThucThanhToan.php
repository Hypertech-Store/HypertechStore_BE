<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhuongThucThanhToan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'ten_phuong_thuc',
        'mo_ta',
    ];

    public function thanhToans()
    {
        return $this->hasMany(ThanhToan::class);
    }
}
