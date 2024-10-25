<?php

use App\Models\KhachHang;
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
        Schema::create('danh_sach_yeu_thich', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KhachHang::class)->constrained();
            $table->foreignIdFor(SanPham::class)->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_sach_yeu_thich');
    }
};
