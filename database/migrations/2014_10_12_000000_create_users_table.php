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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->uuid('user_uuid')->unique();
            $table->string('nama_user', 100); // Optional limit
            $table->string('username', 50)->unique(); // Optional limit
            $table->string('password', 60); // bcrypt length
            $table->enum('role', ['BAAKPSI', 'Warek 3', 'PMB']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
