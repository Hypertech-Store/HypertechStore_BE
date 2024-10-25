<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinhKem extends Model
{
    use HasFactory;
    protected $fillable = [
        'danh_gia_id',
        'duong_dan_tep',
        'loai_tep',

    ];

    public function danhGia()
    {
        return $this->belongsTo(DanhGia::class);
    }
}
