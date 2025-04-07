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
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->bigIncrements('kelurahan_id');
            $table->uuid('kelurahan_uuid')->unique();
            $table->string('nama_kelurahan')->unique();
            $table->string('warna_kelurahan');
            $table->json('geojson_kelurahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};
