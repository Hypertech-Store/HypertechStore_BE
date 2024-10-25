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
        Schema::create('bien_the_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SanPham::class)->constrained();

            $table->string('ten_bien_the')->nullable(false);
            $table->string('gia_tri_bien_the')->nullable(false);
            $table->decimal('gia', 10, 2)->nullable(false);
            $table->integer('so_luong_kho')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_the_san_pham');
    }
};
