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
        Schema::create('quan_tri_viens', function (Blueprint $table) {
            $table->id();
            $table->string('ten_dang_nhap', 50)->unique()->notNull();
            $table->string('mat_khau', 255)->notNull();
            $table->string('ho_ten', 255)->nullable();
            $table->string('email', 255)->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quan_tri_vien');
    }
};
