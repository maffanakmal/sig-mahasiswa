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
        Schema::create('daerah', function (Blueprint $table) {
            $table->id('daerah_id');
            $table->uuid('daerah_uuid')->unique();
            $table->string('nama_geojson_daerah')->unique();
            $table->string('file_geojson_daerah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daerah');
    }
};
