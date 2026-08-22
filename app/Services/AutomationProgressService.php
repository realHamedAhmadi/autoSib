<?php

namespace App\Services;

use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Support\AutomationStatuses;

class AutomationProgressService
{
    /**
     * Recalculate progress counters and status for a single RunUser.
     */
    public function refreshRunUser(int $runUserId): void
    {
        $runUser = AutomationRunUser::query()
            ->with('cares')
            ->find($runUserId);

        if (! $runUser) {
            return;
        }

        $cares = $runUser->cares;

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
            $runUser->save();
            return;
        }

        $hasRunning = $cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_RUNNING);
        $hasPending = $cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_PENDING);
        $hasFailed  = $cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_FAILED);

        $allFinished = $cares->every(fn ($care) => in_array($care->status, [
            AutomationStatuses::CARE_DONE,
            AutomationStatuses::CARE_SKIPPED,
            AutomationStatuses::CARE_FAILED,
        ], true));

        if ($hasRunning) {
            $runUser->status = AutomationStatuses::USER_RUNNING;
            $runUser->finished_at = null;
        } elseif ($allFinished) {
            // All cares are finished; mark failed if any care failed, otherwise done
            $runUser->status = $hasFailed
                ? AutomationStatuses::USER_FAILED
                : AutomationStatuses::USER_DONE;
            $runUser->finished_at = $runUser->finished_at ?? now();
        } elseif ($hasPending && $processedCares > 0) {
            // In-between cares execution
            $runUser->status = AutomationStatuses::USER_RUNNING;
            $runUser->finished_at = null;
        }

        $runUser->save();
    }

    /**
     * Recalculate progress counters and status for the entire AutomationRun.
     */
    public function refreshRun(int $runId): void
    {
        $run = AutomationRun::query()
            ->with('users.cares')
            ->find($runId);

        if (! $run) {
            return;
        }

        $users = $run->users;

        $run->processed_users = $users
            ->whereIn('status', [
                AutomationStatuses::USER_DONE,
                AutomationStatuses::USER_FAILED,
                AutomationStatuses::USER_PAUSED,
            ])
            ->count();

        $run->processed_cares = $users->sum(function ($user) {
            return $user->cares
                ->whereIn('status', [
                    AutomationStatuses::CARE_DONE,
                    AutomationStatuses::CARE_FAILED,
                    AutomationStatuses::CARE_SKIPPED,
                ])
                ->count();
        });

        if ($users->isEmpty()) {
            $run->status = AutomationStatuses::RUN_PENDING;
            $run->finished_at = null;
            $run->save();
            return;
        }

        $allDone = $users->every(fn ($u) => $u->status === AutomationStatuses::USER_DONE);
        $allFailed = $users->every(fn ($u) => $u->status === AutomationStatuses::USER_FAILED);
        $hasRunning = $users->contains(fn ($u) => $u->status === AutomationStatuses::USER_RUNNING);
        $hasPending = $users->contains(fn ($u) => $u->status === AutomationStatuses::USER_PENDING);
        $hasFailed = $users->contains(fn ($u) => $u->status === AutomationStatuses::USER_FAILED);
        $hasPaused = $users->contains(fn ($u) => $u->status === AutomationStatuses::USER_PAUSED);

        $allTerminal = $users->every(fn ($u) => in_array($u->status, [
            AutomationStatuses::USER_DONE,
            AutomationStatuses::USER_FAILED,
            AutomationStatuses::USER_PAUSED,
        ], true));

        if ($hasRunning) {
            $run->status = AutomationStatuses::RUN_RUNNING;
            $run->finished_at = null;
        } elseif ($allTerminal) {
            if ($allDone) {
                $run->status = AutomationStatuses::RUN_DONE;
            } elseif ($allFailed) {
                $run->status = AutomationStatuses::RUN_FAILED;
            } else {
                $run->status = AutomationStatuses::RUN_PARTIAL_FAILED;
            }
            $run->finished_at = $run->finished_at ?? now();
        } elseif ($run->processed_users > 0 || $run->processed_cares > 0) {
            // Run has started and has pending users left
            $run->status = AutomationStatuses::RUN_RUNNING;
            $run->finished_at = null;
        } else {
            $run->status = AutomationStatuses::RUN_PENDING;
            $run->finished_at = null;
        }

        $run->save();
    }
}
