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
            $table->bigInteger('kode_daerah')->unsigned()->primary(); // PK dari BPS
            $table->uuid('daerah_uuid')->unique();
            $table->string('nama_daerah');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
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
