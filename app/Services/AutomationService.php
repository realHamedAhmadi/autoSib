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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutomationService
{
    /**
     * Start a new automation run for given users and care type.
     */
    public function run(array $users, CareType $careType)
    {
        $run=new \stdClass();
        $run->id=-1;
        $eligibleUsers = $this->filterEligibleUsers($users);

        if (empty($eligibleUsers)) {
            return $run;
        }

        // Cache cares list and count in memory to avoid repeated queries inside loops
        $cares = Care::type($careType)->get();
        $careCount = $cares->count();

        if ($careCount === 0) {
            return $run;
        }

        $totalUsers = count($eligibleUsers);
        $totalCares = $totalUsers * $careCount;

        $run = DB::transaction(function () use ($eligibleUsers, $careType, $cares, $totalUsers, $totalCares, $careCount) {
            $run = AutomationRun::create([
                'user_id'         => getCurrentUserId(),
                'status'          => AutomationStatuses::RUN_PENDING,
                'total_users'     => $totalUsers,
                'processed_users' => 0,
                'total_cares'     => $totalCares,
                'processed_cares' => 0,
                'input'           => [
                    'care_type' => $careType,
                ],
            ]);

            foreach ($eligibleUsers as $user) {
                $runUser = AutomationRunUser::create([
                    'automation_run_id' => $run->id,
                    'sib_user_id'       => $user['id'],
                    'status'            => AutomationStatuses::USER_PENDING,
                    'total_cares'       => $careCount,
                    'processed_cares'   => 0,
                    'payload'           => $user,
                ]);

                $picker = new RandomNumberPicker($careCount);

                foreach ($cares as $care) {
                    AutomationRunUserCare::create([
                        'automation_run_user_id' => $runUser->id,
                        'care_id'                => $care->id,
                        'sort_order'             => $picker->next(),
                        'status'                 => AutomationStatuses::CARE_PENDING,
                        'payload'                => null,
                    ]);
                }
            }

            return $run;
        });

        ProcessAutomationRunUserJob::dispatch($run);

        return $run;
    }

    /**
     * Retry an existing failed or cancelled automation run.
     */
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

        if (!$lock->get()) {
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

                $failedUserIds = AutomationRunUser::query()
                    ->where('automation_run_id', $run->id)
                    ->where('status', AutomationStatuses::USER_FAILED)
                    ->pluck('id');

                AutomationRunUser::query()
                    ->whereIn('id', $failedUserIds)
                    ->update([
                        'status'          => AutomationStatuses::USER_PENDING,
                        'processed_cares' => 0,
                        'started_at'      => null,
                        'finished_at'     => null,
                        'error_message'   => null,
                    ]);

                AutomationRunUserCare::query()
                    ->whereIn('automation_run_user_id', $failedUserIds)
                    ->where('status', AutomationStatuses::CARE_FAILED)
                    ->update([
                        'status'        => AutomationStatuses::CARE_PENDING,
                        'started_at'    => null,
                        'finished_at'   => null,
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
                    'status'          => AutomationStatuses::RUN_PENDING,
                    'processed_users' => $processedUsers,
                    'processed_cares' => $processedCares,
                    'started_at'      => null,
                    'finished_at'     => null,
                    'error_message'   => null,
                ]);
            });

            ProcessAutomationRunUserJob::dispatch($run);

            return $run;
        } finally {
            $lock->release();
        }
    }

    /**
     * Filter given users by today's duplicate exclusion and user quota limit.
     */
    protected function filterEligibleUsers(array $users): array
    {
        $todayProcessedSibUserIds = $this->getTodayProcessedSibUserIds();
        $todayProcessedCount = count($todayProcessedSibUserIds);

        $currentUser = getCurrentUser();
        $maxUserQuota = $currentUser?->max_user_care;

        // Check if daily quota is already exhausted
        if (!is_null($maxUserQuota) && $todayProcessedCount >= $maxUserQuota) {
            return [];
        }

        // Exclude users already cared for today
        $freshUsers = array_values(array_filter($users, function (array $user) use ($todayProcessedSibUserIds) {
            return !in_array($user['id'], $todayProcessedSibUserIds, true);
        }));

        if (empty($freshUsers)) {
            return [];
        }

        // Slice by remaining quota if configured
        if (!is_null($maxUserQuota)) {
            $remainingAllowed = max(0, $maxUserQuota - $todayProcessedCount);
            return array_slice($freshUsers, 0, $remainingAllowed);
        }

        return $freshUsers;
    }

    /**
     * Retrieve list of SIB user IDs processed today by current authenticated user.
     */
    protected function getTodayProcessedSibUserIds(): array
    {
        return AutomationRunUser::query()
            ->whereHas('run', function ($query) {
                $query->where('user_id', getCurrentUserId());
            })
            ->whereDate('created_at', now()->toDateString())
            ->distinct()
            ->pluck('sib_user_id')
            ->toArray();
    }
}
