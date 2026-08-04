<?php

namespace App\Services;

use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Support\AutomationStatuses;

class AutomationProgressService
{
    /**
     * Recalculates and updates the status, processed count, and timestamps of a single RunUser.
     *
     * @param int $runUserId
     * @return void
     */
    public function refreshRunUser(int $runUserId): void
    {
        $runUser = AutomationRunUser::with('cares')->find($runUserId);

        if (! $runUser) {
            return;
        }

        $cares = $runUser->cares;

        // Count completed, failed, or skipped cares as processed
        $processedCares = $cares
            ->whereIn('status', [
                AutomationStatuses::CARE_DONE,
                AutomationStatuses::CARE_FAILED,
                AutomationStatuses::CARE_SKIPPED,
            ])
            ->count();

        $runUser->processed_cares = $processedCares;

        if ($cares->isEmpty()) {
            $runUser->status = AutomationStatuses::USER_PENDING;
            $runUser->finished_at = null;
        } else {
            // Determine status based on care collection states
            $allFinishedOrSkipped = $cares->every(fn ($care) => in_array($care->status, [
                AutomationStatuses::CARE_DONE,
                AutomationStatuses::CARE_SKIPPED,
            ], true));

            $hasFailed = $cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_FAILED);
            $hasRunning = $cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_RUNNING);

            if ($allFinishedOrSkipped) {
                $runUser->status = AutomationStatuses::USER_DONE;
                $runUser->finished_at = $runUser->finished_at ?? now();
                $runUser->error_message = null;
            } elseif ($hasRunning) {
                $runUser->status = AutomationStatuses::USER_RUNNING;
                $runUser->finished_at = null;
            } elseif ($hasFailed) {
                $runUser->status = AutomationStatuses::USER_PAUSED;
                $runUser->finished_at = null;
            } else {
                $runUser->status = AutomationStatuses::USER_PENDING;
                $runUser->finished_at = null;
            }
        }

        $runUser->save();
    }

    /**
     * Recalculates and updates the status, processed count, and timestamps of the entire Run.
     *
     * @param int $runId
     * @return void
     */
    public function refreshRun(int $runId): void
    {
        $run = AutomationRun::with('users.cares')->find($runId);

        if (! $run) {
            return;
        }

        $users = $run->users;

        // Count users in terminal or paused states as processed
        $run->processed_users = $users
            ->whereIn('status', [
                AutomationStatuses::USER_DONE,
                AutomationStatuses::USER_FAILED,
                AutomationStatuses::USER_PAUSED,
            ])
            ->count();

        // Calculate sum of processed cares across all users
        $run->processed_cares = $users->sum(function ($user) {
            return $user->cares->whereIn('status', [
                AutomationStatuses::CARE_DONE,
                AutomationStatuses::CARE_FAILED,
                AutomationStatuses::CARE_SKIPPED,
            ])->count();
        });

        if ($users->isEmpty()) {
            $run->status = AutomationStatuses::RUN_PENDING;
            $run->finished_at = null;
        } else {
            // Determine status based on user collection states
            $allDone = $users->every(fn ($user) => $user->status === AutomationStatuses::USER_DONE);
            $hasRunning = $users->contains(fn ($user) => $user->status === AutomationStatuses::USER_RUNNING);
            $hasPaused = $users->contains(fn ($user) => $user->status === AutomationStatuses::USER_PAUSED);
            $hasFailed = $users->contains(fn ($user) => $user->status === AutomationStatuses::USER_FAILED);
            $hasPending = $users->contains(fn ($user) => $user->status === AutomationStatuses::USER_PENDING);

            if ($allDone) {
                $run->status = AutomationStatuses::RUN_DONE;
                $run->finished_at = $run->finished_at ?? now();
            } elseif ($hasRunning) {
                $run->status = AutomationStatuses::RUN_RUNNING;
                $run->finished_at = null;
            } elseif ($hasPaused || $hasFailed) {
                $run->status = AutomationStatuses::RUN_PARTIAL_FAILED;
                $run->finished_at = null;
            } elseif ($hasPending) {
                $run->status = AutomationStatuses::RUN_PENDING;
                $run->finished_at = null;
            } else {
                // Fallback state
                $run->status = AutomationStatuses::RUN_PENDING;
                $run->finished_at = null;
            }
        }

        $run->save();
    }
}
