<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // Example: sib_user_care_batch
            $table->string('status')->default('pending')->index(); // pending, running, done, partial_failed, failed, cancelled

            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('processed_users')->default(0);

            $table->unsignedInteger('total_cares')->default(0);
            $table->unsignedInteger('processed_cares')->default(0);

            $table->json('input')->nullable();   // Original request payload
            $table->json('result')->nullable();  // Aggregated final result

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
