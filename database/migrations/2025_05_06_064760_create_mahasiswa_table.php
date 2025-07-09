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
            $table->integer('nim')->unsigned()->primary(); // Primary Key
            $table->uuid('mahasiswa_uuid')->unique();
            $table->year('tahun_masuk');
        
            // Foreign keys, sekarang bisa NULL
            $table->integer('kode_prodi')->unsigned()->nullable();
            $table->integer('npsn')->unsigned()->nullable();
            $table->integer('kode_daerah')->unsigned()->nullable();
        
            // Foreign key constraints
            $table->foreign('kode_prodi')->references('kode_prodi')->on('prodi')->onDelete('cascade');
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
