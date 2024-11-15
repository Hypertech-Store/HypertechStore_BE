<?php

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
        Schema::create('sale_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SanPham::class)->constrained();
            $table->decimal('sale_theo_phan_tram', 5, 2);
            $table->date('ngay_bat_dau_sale');
            $table->date('ngay_ket_thuc_sale');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_sanphams');
    }
};
