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
            $table->bigInteger('nim')->unsigned()->primary(); // Primary Key
            $table->uuid('mahasiswa_uuid')->unique();
            $table->smallInteger('tahun_masuk');
        
            // Foreign keys, sekarang bisa NULL
            $table->integer('kode_jurusan')->unsigned()->nullable();
            $table->bigInteger('npsn')->unsigned()->nullable();
            $table->bigInteger('kode_daerah')->unsigned()->nullable();
        
            // Foreign key constraints
            $table->foreign('kode_jurusan')->references('kode_jurusan')->on('jurusan')->onDelete('cascade');
            $table->foreign('npsn')->references('npsn')->on('sekolah')->onDelete('cascade');
            $table->foreign('kode_daerah')->references('kode_daerah')->on('daerah')->onDelete('cascade');
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
