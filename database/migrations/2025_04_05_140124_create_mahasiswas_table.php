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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->bigIncrements('mahasiswa_id');
            $table->uuid('mahasiswa_uuid');
            $table->string('nama_mahasiswa');
            $table->string('nim');
            $table->string('tahun_masuk');
            $table->string('jurusan');
            $table->string('sekolah_asal');
            $table->string('daerah_asal');
            $table->string('status_mahasiswa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
