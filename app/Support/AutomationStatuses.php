<?php

namespace App\Support;

final class AutomationStatuses
{
    // AutomationRun Statuses
    public const RUN_PENDING = 'pending';
    public const RUN_RUNNING = 'running';
    public const RUN_DONE = 'done';
    public const RUN_PARTIAL_FAILED = 'partial_failed';
    public const RUN_FAILED = 'failed';
    public const RUN_CANCELLED = 'cancelled';

    // AutomationRunUser Statuses
    public const USER_PENDING = 'pending';
    public const USER_RUNNING = 'running';
    public const USER_PAUSED = 'paused';
    public const USER_DONE = 'done';
    public const USER_FAILED = 'failed';

    // AutomationRunUserCare Statuses
    public const CARE_PENDING = 'pending';
    public const CARE_RUNNING = 'running';
    public const CARE_DONE = 'done';
    public const CARE_FAILED = 'failed';
    public const CARE_SKIPPED = 'skipped';
}
