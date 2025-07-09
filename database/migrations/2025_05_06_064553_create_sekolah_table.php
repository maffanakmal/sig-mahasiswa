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
        Schema::create('sekolah', function (Blueprint $table) {
            $table->integer('npsn')->unsigned()->primary();
            $table->uuid('sekolah_uuid')->unique();
            $table->string('nama_sekolah', 100);
            $table->text('alamat_sekolah');
            $table->integer('kode_daerah')->unsigned()->nullable();
            $table->decimal('latitude_sekolah', 18, 15);
            $table->decimal('longitude_sekolah', 18, 15);

            $table->foreign('kode_daerah')->references('kode_daerah')->on('daerah')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah');
    }
};
