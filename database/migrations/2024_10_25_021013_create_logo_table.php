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
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('ten_logo', 255)->nullable();
            $table->string('hinh_anh', 255)->notNull();
            $table->enum('trang_thai', ['Hiển thị', 'Ẩn'])->default('Hiển thị');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('logo');
    }
};
