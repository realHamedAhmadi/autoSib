<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cares', function (Blueprint $table) {
            $table->string('type',64)->change();
        });
        DB::statement('ALTER TABLE cares DROP CONSTRAINT IF EXISTS cares_type_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
