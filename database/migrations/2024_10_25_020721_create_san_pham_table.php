<?php

use App\Models\DanhMuc;
use App\Models\DanhMucCon;
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
        Schema::create('san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DanhMuc::class)->constrained();
            $table->foreignIdFor(DanhMucCon::class)->constrained();

            $table->string('ten_san_pham')->nullable(false);
            $table->text('mo_ta')->nullable();
            $table->decimal('gia', 10, 2)->nullable(false);
            $table->integer('so_luong_ton_kho')->default(0);
            $table->string('duong_dan_anh')->nullable();
            $table->integer('luot_xem')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_pham');
    }
};
