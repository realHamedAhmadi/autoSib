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
        Schema::create('family_env_healths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('plumbing')->default(0);
            $table->tinyInteger('water_sources')->default(0);
            $table->tinyInteger('toilet')->default(0);
            $table->tinyInteger('sewage')->default(0);
            $table->tinyInteger('waste')->default(0);
            $table->tinyInteger('has_livestock')->default(0);
            $table->tinyInteger('animal_waste')->default(0);
            $table->tinyInteger('has_city_gas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_env_healths');
    }
};
