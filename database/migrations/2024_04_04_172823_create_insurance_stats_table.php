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
        Schema::create('insurance_stats', function (Blueprint $table) {
            $table->id();
            $table->string('stat',15);
            $table->unsignedInteger('center_code');
            $table->string('center_name',30);
            $table->unsignedInteger('unit_code');
            $table->string('unit_name',100);
            $table->string('type',30);
            $table->string('type_code',30);
            $table->enum('gender',[
                'men','women'
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
        Schema::dropIfExists('insurance_stats');
    }
};
