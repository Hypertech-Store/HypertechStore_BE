<?php

use App\Models\DanhGia;
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
        Schema::create('dinh_kems', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DanhGia::class)->constrained();

            $table->string('duong_dan_tep', 255)->notNull();
            $table->enum('loai_tep', ['Hinh anh', 'Video'])->notNull();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dinh_kem');
    }
};
