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
        Schema::create('family_env_health_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('col_22173',10)->nullable();
            $table->string('col_31518',10)->nullable();
            $table->string('col_31502',10)->nullable();
            $table->string('col_31503',10)->nullable();
            $table->string('col_31504',10)->nullable();
            $table->string('col_31505',10)->nullable();
            $table->string('col_31507',10)->nullable();
            $table->string('col_22190',10)->nullable();
            $table->string('col_31517',10)->nullable();
            $table->string('col_31511',10)->nullable();

            $table->tinyInteger('col_31498_128850')->default(0);
            $table->tinyInteger('col_31498_128851')->default(0);
            $table->tinyInteger('col_31498_128852')->default(0);
            $table->tinyInteger('col_31496_128844')->default(0);
            $table->tinyInteger('col_31496_128845')->default(0);
            $table->tinyInteger('col_31496_128846')->default(0);
            $table->tinyInteger('col_31496_128847')->default(0);
            $table->tinyInteger('col_31496_128848')->default(0);
            $table->tinyInteger('col_31496_128849')->default(0);
            $table->tinyInteger('col_31508_128903')->default(0);
            $table->tinyInteger('col_31508_128901')->default(0);
            $table->tinyInteger('col_31500_128854')->default(0);
            $table->tinyInteger('col_31500_128855')->default(0);
            $table->tinyInteger('col_31500_128856')->default(0);
            $table->tinyInteger('col_31500_128857')->default(0);
            $table->tinyInteger('col_31501')->default(0);
            $table->tinyInteger('col_31506')->default(0);
            $table->tinyInteger('col_31497')->default(0);
            $table->tinyInteger('col_31499')->default(0);
            $table->tinyInteger('col_31512')->default(0);
            $table->tinyInteger('col_22183')->default(0);
            $table->tinyInteger('col_22182')->default(0);
            $table->tinyInteger('col_22184')->default(0);
            $table->tinyInteger('col_22189')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_env_health_forms');
    }
};
