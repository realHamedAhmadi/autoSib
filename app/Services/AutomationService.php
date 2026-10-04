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
        $eligibleUsers = $this->filterEligibleUsers($users,$careType);

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
     * Filter given users by today's duplicate exclusion for specific care type and overall user quota limit.
     */
    protected function filterEligibleUsers(array $users, CareType $careType): array
    {
        // Retrieve all processed care records for today
        $todayProcessedRecords = $this->getTodayProcessedSibUsers();

        // Total processed cares today across all types (used for overall daily quota)
        $todayProcessedCount = $todayProcessedRecords->count();

        $currentUser = getCurrentUser();
        $maxUserQuota = $currentUser?->max_user_care;

        // Check if total daily quota is already exhausted
        if (!is_null($maxUserQuota) && $todayProcessedCount >= $maxUserQuota) {
            return [];
        }

        // Extract the raw value of the care type argument
        $careTypeName = $careType->name;

        // Build lookup set of sib_user_ids that specifically received THIS care_type today
        $processedForThisCareTypeLookup = $todayProcessedRecords
            ->where('care_type', $careTypeName)
            ->pluck('sib_user_id')
            ->flip()
            ->all();

        // Exclude user only if they have already received THIS specific care_type today
        // (If the user received a different care_type earlier, they will NOT be excluded)
        $freshUsers = array_values(array_filter($users, function (array $user) use ($processedForThisCareTypeLookup) {
            return !isset($processedForThisCareTypeLookup[$user['id']]);
        }));

        if (empty($freshUsers)) {
            return [];
        }

        // Slice by remaining total quota if configured
        if (!is_null($maxUserQuota)) {
            $remainingAllowed = max(0, $maxUserQuota - $todayProcessedCount);
            return array_slice($freshUsers, 0, $remainingAllowed);
        }

        return $freshUsers;
    }

    /**
     * Retrieve list of all unique (sib_user_id, care_type) pairs processed today by current authenticated user.
     */
    protected function getTodayProcessedSibUsers()
    {
        return AutomationRunUser::query()
            // Join intermediate table (automation_run_user_cares)
            ->join('automation_run_user_cares', 'automation_run_users.id', '=', 'automation_run_user_cares.automation_run_user_id')
            // Join cares table to access care type
            ->join('cares', 'automation_run_user_cares.care_id', '=', 'cares.id')
            // Filter by run owner
            ->whereHas('run', function ($query) {
                $query->where('user_id', getCurrentUserId());
            })
            // Filter by creation date
            ->whereDate('automation_run_users.created_at', now()->toDateString())
            // Select required fields and alias cares.type to care_type
            ->select([
                'automation_run_users.sib_user_id',
                'cares.type as care_type',
            ])
            // Ensure unique pairs of sib_user_id and care_type
            ->distinct()
            ->get();
    }

}
