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
        Schema::create('bai_viet', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255)->notNull();
            $table->text('noi_dung')->notNull();
            $table->string('tac_gia', 255)->nullable();
            $table->enum('trang_thai', ['Công khai', 'Riêng tư'])->default('Công khai');
            $table->string('hinh_anh', 255)->nullable();
            $table->integer('so_luot_xem')->default(0);
            $table->float('danh_gia')->default(0);
            $table->integer('so_binh_luan')->default(0);
            $table->string('tag', 255)->nullable();
            $table->enum('loai_bai_viet', ['Tin tức', 'Blog', 'Thông báo'])->default('Tin tức');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bai_viet');
    }
};
