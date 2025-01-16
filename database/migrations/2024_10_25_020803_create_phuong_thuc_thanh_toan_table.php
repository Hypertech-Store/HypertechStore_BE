<?php

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
        Schema::create('phuong_thuc_thanh_toans', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phuong_thuc')->nullable(false);
            $table->text('mo_ta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phuong_thuc_thanh_toan');
    }
};
