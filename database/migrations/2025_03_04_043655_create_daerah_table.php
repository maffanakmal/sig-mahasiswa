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
            $table->smallInteger('kode_daerah')->unsigned()->primary();
            $table->uuid('daerah_uuid')->unique();
            $table->string('nama_daerah', 100);
            $table->string('latitude_daerah', 20);
            $table->string('longitude_daerah', 20);
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
