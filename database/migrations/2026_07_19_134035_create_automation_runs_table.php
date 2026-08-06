<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status',64)->default(\App\Support\AutomationStatuses::RUN_PENDING)->index();

            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('processed_users')->default(0);

            $table->unsignedInteger('total_cares')->default(0);
            $table->unsignedInteger('processed_cares')->default(0);

            $table->json('input')->nullable();
            $table->json('result')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
    }
};
