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
            $table->increments('user_id');
            $table->uuid('user_uuid')->unique();
            $table->string('nama_lengkap', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 50)->unique()->nullable();
            $table->string('password', 60);
            $table->boolean('is_active')->default(0);
            $table->timestamp('last_active')->nullable();
            $table->uuid('reset_token')->nullable();
            $table->timestamp('token_expire')->nullable();
            $table->timestamps();
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
