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
            $table->string('tahun_masuk');
        
            // Foreign keys, sekarang bisa NULL
            $table->bigInteger('jurusan')->unsigned()->nullable();
            $table->bigInteger('sekolah_asal')->unsigned()->nullable();
            $table->bigInteger('daerah_asal')->unsigned()->nullable();
        
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('jurusan')->references('kode_jurusan')->on('jurusan')->onDelete('cascade');
            $table->foreign('sekolah_asal')->references('sekolah_id')->on('sekolah')->onDelete('cascade');
            $table->foreign('daerah_asal')->references('kode_daerah')->on('daerah')->onDelete('cascade');
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
