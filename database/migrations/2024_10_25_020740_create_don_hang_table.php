<?php

use App\Models\KhachHang;
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
        Schema::create('don_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(KhachHang::class)->constrained();

            $table->string('trang_thai_don_hang')->default('Chờ xử lý');
            $table->decimal('tong_tien', 10, 2)->nullable(false);
            $table->string('dia_chi_giao_hang')->nullable();
            $table->string('phuong_thuc_thanh_toan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hang');
    }
};
