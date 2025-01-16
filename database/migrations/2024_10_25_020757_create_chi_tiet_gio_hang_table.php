<?php

use App\Models\BienTheSanPham;
use App\Models\ChiTietGioHang;
use App\Models\GioHang;
use App\Models\SanPham;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chi_tiet_gio_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GioHang::class)->constrained();
            $table->foreignIdFor(SanPham::class)->constrained();
            $table->foreignIdFor(BienTheSanPham::class)->constrained();

            $table->integer('so_luong')->nullable(false);
            $table->decimal('gia', 10, 2)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_gio_hang');
    }
};
