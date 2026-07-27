<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAutomationRunUserJob;
use App\Models\AutomationRunUser;
use App\Support\AutomationStatuses;
use Illuminate\Http\JsonResponse;

class ResumeAutomationRunUserController extends Controller
{
    public function __invoke(AutomationRunUser $runUser): JsonResponse
    {
        if (! in_array($runUser->status, [
            AutomationStatuses::USER_PAUSED,
            AutomationStatuses::USER_FAILED,
            AutomationStatuses::USER_PENDING,
        ], true)) {
            return response()->json([
                'message' => 'User automation is not in a resumable state.',
            ], 422);
        }

        ProcessAutomationRunUserJob::dispatch($runUser->id);

        return response()->json([
            'message' => 'User automation job resumed.',
            'run_user_id' => $runUser->id,
        ]);
    }
}
