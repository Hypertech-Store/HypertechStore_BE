<?php

use App\Models\DonHang;
use App\Models\TrangThaiGiaoHang;
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
        Schema::create('giao_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DonHang::class)->constrained();
            $table->foreignIdFor(TrangThaiGiaoHang::class)->constrained();

            $table->string('phuong_thuc_giao_hang', 50)->nullable();
            $table->decimal('phi_giao_hang', 10, 2)->nullable();
            $table->timestamp('ngay_giao_hang')->nullable();
            $table->timestamp('ngay_nhan_hang')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giao_hang');
    }
};
