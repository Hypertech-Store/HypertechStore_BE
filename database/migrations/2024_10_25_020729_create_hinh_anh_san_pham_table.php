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
        Schema::create('hinh_anh_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SanPham::class)->constrained();

            $table->string('duong_dan_hinh_anh')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hinh_anh_san_pham');
    }
};
