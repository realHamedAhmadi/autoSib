<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_run_user_cares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_run_user_id')
                ->constrained('automation_run_users')
                ->cascadeOnDelete();
            $table->foreignId('care_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('status')
                ->default(\App\Support\AutomationStatuses::CARE_PENDING)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->json('checkpoint')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['automation_run_user_id', 'sort_order']);
            $table->index(['automation_run_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_run_user_cares');
    }
};
