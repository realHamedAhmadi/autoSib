<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAutomationRunUserJob;
use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Models\AutomationRunUserCare;
use App\Support\AutomationStatuses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StartAutomationRunController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'users' => ['required', 'array', 'min:1'],
            'users.*.sib_user_id' => ['required', 'string'],
            'users.*.payload' => ['nullable', 'array'],
            'users.*.cares' => ['required', 'array', 'min:1'],
            'users.*.cares.*.care_code' => ['required', 'string'],
            'users.*.cares.*.care_title' => ['required', 'string'],
            'users.*.cares.*.care_type' => ['nullable', 'string'],
            'users.*.cares.*.payload' => ['nullable', 'array'],
        ]);

        $run = DB::transaction(function () use ($validated) {
            $totalUsers = count($validated['users']);
            $totalCares = collect($validated['users'])->sum(fn ($user) => count($user['cares']));

            $run = AutomationRun::create([
                'type' => $validated['type'],
                'status' => AutomationStatuses::RUN_PENDING,
                'total_users' => $totalUsers,
                'processed_users' => 0,
                'total_cares' => $totalCares,
                'processed_cares' => 0,
                'input' => $validated,
            ]);

            foreach ($validated['users'] as $userData) {
                $runUser = AutomationRunUser::create([
                    'automation_run_id' => $run->id,
                    'sib_user_id' => $userData['sib_user_id'],
                    'status' => AutomationStatuses::USER_PENDING,
                    'total_cares' => count($userData['cares']),
                    'processed_cares' => 0,
                    'payload' => $userData['payload'] ?? null,
                ]);

                foreach ($userData['cares'] as $index => $careData) {
                    AutomationRunUserCare::create([
                        'automation_run_user_id' => $runUser->id,
                        'care_code' => $careData['care_code'],
                        'care_title' => $careData['care_title'],
                        'care_type' => $careData['care_type'] ?? null,
                        'sort_order' => $index + 1,
                        'status' => AutomationStatuses::CARE_PENDING,
                        'payload' => $careData['payload'] ?? null,
                    ]);
                }
            }

            return $run;
        });

        foreach ($run->users()->pluck('id') as $runUserId) {
            ProcessAutomationRunUserJob::dispatch($runUserId);
        }

        return response()->json([
            'message' => 'Automation run initialized and dispatched.',
            'run_id' => $run->id,
        ], 201);
    }
}
