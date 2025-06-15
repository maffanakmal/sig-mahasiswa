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
            $table->bigInteger('npsn')->unsigned()->primary();
            $table->uuid('sekolah_uuid')->unique();
            $table->string('nama_sekolah', 100);
            $table->string('alamat_sekolah', 255);
            $table->bigInteger('kode_daerah')->unsigned()->nullable();
            $table->string('latitude_sekolah', 20);
            $table->string('longitude_sekolah', 20);

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
