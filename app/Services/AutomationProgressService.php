<?php

namespace App\Services;

use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Support\AutomationStatuses;

class AutomationProgressService
{
    public function refreshRunUser(int $runUserId): void
    {
        $runUser = AutomationRunUser::with('cares')->find($runUserId);

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
        } elseif ($cares->every(fn ($care) => $care->status === AutomationStatuses::CARE_DONE)) {
            $runUser->status = AutomationStatuses::USER_DONE;
            $runUser->finished_at = now();
            $runUser->error_message = null;
        } elseif ($cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_FAILED)) {
            $runUser->status = AutomationStatuses::USER_PAUSED;
            $runUser->finished_at = null;
        } elseif ($cares->contains(fn ($care) => $care->status === AutomationStatuses::CARE_RUNNING)) {
            $runUser->status = AutomationStatuses::USER_RUNNING;
            $runUser->finished_at = null;
        } else {
            $runUser->status = AutomationStatuses::USER_PENDING;
            $runUser->finished_at = null;
        }

        $runUser->save();
    }

    public function refreshRun(int $runId): void
    {
        $run = AutomationRun::with('users.cares')->find($runId);

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
            return $user->cares->whereIn('status', [
                AutomationStatuses::CARE_DONE,
                AutomationStatuses::CARE_FAILED,
                AutomationStatuses::CARE_SKIPPED,
            ])->count();
        });

        if ($users->isEmpty()) {
            $run->status = AutomationStatuses::RUN_PENDING;
        } elseif ($users->every(fn ($user) => $user->status === AutomationStatuses::USER_DONE)) {
            $run->status = AutomationStatuses::RUN_DONE;
            $run->finished_at = now();
        } elseif ($users->contains(fn ($user) => $user->status === AutomationStatuses::USER_RUNNING)) {
            $run->status = AutomationStatuses::RUN_RUNNING;
            $run->finished_at = null;
        } elseif ($users->contains(fn ($user) => $user->status === AutomationStatuses::USER_PAUSED)) {
            $run->status = AutomationStatuses::RUN_PARTIAL_FAILED;
            $run->finished_at = null;
        } elseif ($users->contains(fn ($user) => $user->status === AutomationStatuses::USER_PENDING)) {
            $run->status = AutomationStatuses::RUN_PENDING;
            $run->finished_at = null;
        }

        $run->save();
    }
}
