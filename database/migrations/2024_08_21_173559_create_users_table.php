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
            $table->id();
            $table->string('code',15)->nullable();
            $table->string('name',64)->nullable();
            $table->string('national_code',15)->nullable()->unique();
            $table->string('role_code',15)->nullable();
            $table->string('unit_code',15)->nullable();
            $table->string('unit_name',100)->nullable();
            $table->string('token',900)->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->timestamps();
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
