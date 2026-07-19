<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('automation_run_users', function (Blueprint $table) {
            $table->foreign('current_care_id')
                ->references('id')
                ->on('automation_run_user_cares')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('automation_run_users', function (Blueprint $table) {
            $table->dropForeign(['current_care_id']);
        });
    }
};
