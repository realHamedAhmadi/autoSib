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
        Schema::create('population_stats', function (Blueprint $table) {
            $table->id();
            $table->string('stat',15);
            $table->enum('ageType',[
                'yearly','ageCategory'
            ]);
            $table->unsignedInteger('center_code');
            $table->string('center_name',30);
            $table->unsignedInteger('unit_code');
            $table->string('unit_name',30);
            $table->string('age_category',64);
            $table->string('from_birthdate',15)->nullable();
            $table->string('to_birthdate',15)->nullable();
            $table->enum('type',[
                'men','women','married_women'
            ]);
            $table->integer('number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('population_stats');
    }
};
