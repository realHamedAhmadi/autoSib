<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_run_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('automation_run_id')
                ->constrained('automation_runs')
                ->cascadeOnDelete();

            $table->string('sib_user_id')->index();
            $table->string('status')->default('pending')->index(); // pending, running, paused, done, failed

            $table->unsignedInteger('total_cares')->default(0);
            $table->unsignedInteger('processed_cares')->default(0);

            $table->unsignedBigInteger('current_care_id')->nullable();

            $table->json('payload')->nullable(); // Original per-user payload
            $table->json('result')->nullable();  // Final per-user result

            $table->text('error_message')->nullable();
            $table->timestamp('last_attempt_at')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['automation_run_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_run_users');
    }
};
