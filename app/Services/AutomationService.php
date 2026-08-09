<?php

namespace App\Services;

use App\Jobs\ProcessAutomationRunUserJob;
use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Models\AutomationRunUserCare;
use App\Models\Care;
use App\Support\AutomationStatuses;
use App\Support\CareType;
use App\Support\RandomNumberPicker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutomationService
{
    public function run(array $users, CareType $careType)
    {
        $run = DB::transaction(function () use ($users, $careType) {
            $totalUsers = count($users);
            $totalCares = collect($users)->sum(fn ($user) => Care::type($careType)->count());

            $run = AutomationRun::create([
                'user_id' => getCurrentUserId(),
                'status' => AutomationStatuses::RUN_PENDING,
                'total_users' => $totalUsers,
                'processed_users' => 0,
                'total_cares' => $totalCares,
                'processed_cares' => 0,
                'input' => [
                    /*'users' => $users,
                    'care_type' => $careType,*/
                ],
            ]);

            foreach ($users as $user) {
                $runUser = AutomationRunUser::create([
                    'automation_run_id' => $run->id,
                    'sib_user_id' => $user['id'],
                    'status' => AutomationStatuses::USER_PENDING,
                    'total_cares' => Care::type($careType)->count(),
                    'processed_cares' => 0,
                    'payload' => $user,
                ]);

                $picker=new RandomNumberPicker(Care::type($careType)->count());

                foreach (Care::type($careType)->get() ?? [] as  $care) {
                    AutomationRunUserCare::create([
                        'automation_run_user_id' => $runUser->id,
                        'care_id' => $care->id,
                        'sort_order' => $picker->next(),
                        'status' => AutomationStatuses::CARE_PENDING,
                        'payload' => $careData['payload'] ?? null,
                    ]);
                }
            }

            return $run;
        });

        ProcessAutomationRunUserJob::dispatch($run);

        return $run;
    }

    public function retry(AutomationRun $run): AutomationRun
    {
        $retryableStatuses = [
            AutomationStatuses::RUN_FAILED,
            AutomationStatuses::RUN_PARTIAL_FAILED,
            AutomationStatuses::RUN_CANCELLED,
        ];

        if (!in_array($run->status, $retryableStatuses, true)) {
            throw ValidationException::withMessages([
                'run' => 'Only failed, partially failed, or cancelled runs can be retried.',
            ]);
        }

        $lock = Cache::lock("automation-run-retry:{$run->id}", 30);

        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'run' => 'This run is already being retried.',
            ]);
        }

        try {
            DB::transaction(function () use ($run, $retryableStatuses) {
                $run->refresh();

                if (!in_array($run->status, $retryableStatuses, true)) {
                    throw ValidationException::withMessages([
                        'run' => 'The run status changed and can no longer be retried.',
                    ]);
                }

                $runUserIds = AutomationRunUser::query()
                    ->where('automation_run_id', $run->id)
                    ->where('status', AutomationStatuses::USER_FAILED)
                    ->pluck('id');

                AutomationRunUser::query()
                    ->whereIn('id', $runUserIds)
                    ->update([
                        'status' => AutomationStatuses::USER_PENDING,
                        'processed_cares' => 0,
                        'started_at' => null,
                        'finished_at' => null,
                        'error_message' => null,
                    ]);

                AutomationRunUserCare::query()
                    ->whereIn('automation_run_user_id', $runUserIds)
                    ->where('status', AutomationStatuses::CARE_FAILED)
                    ->update([
                        'status' => AutomationStatuses::CARE_PENDING,
                        'started_at' => null,
                        'finished_at' => null,
                        'error_message' => null,
                    ]);

                $processedUsers = AutomationRunUser::query()
                    ->where('automation_run_id', $run->id)
                    ->where('status', AutomationStatuses::USER_DONE)
                    ->count();

                $processedCares = AutomationRunUserCare::query()
                    ->whereIn('automation_run_user_id', function ($query) use ($run) {
                        $query->select('id')
                            ->from('automation_run_users')
                            ->where('automation_run_id', $run->id);
                    })
                    ->where('status', AutomationStatuses::CARE_DONE)
                    ->count();

                $run->update([
                    'status' => AutomationStatuses::RUN_PENDING,
                    'processed_users' => $processedUsers,
                    'processed_cares' => $processedCares,
                    'started_at' => null,
                    'finished_at' => null,
                    'error_message' => null,
                ]);
            });

            ProcessAutomationRunUserJob::dispatch($run);

            return $run;
        } finally {
            $lock->release();
        }
    }
}
