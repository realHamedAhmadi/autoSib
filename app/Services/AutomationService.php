<?php

namespace App\Services;

use App\Jobs\ProcessAutomationRunUserJob;
use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Models\AutomationRunUserCare;
use App\Models\Care;
use App\Support\AutomationStatuses;
use App\Support\CareType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutomationService
{

    function run(array $users,CareType $careType)
    {
        $run = DB::transaction(function () use ($users,$careType) {
            $totalUsers = count($users);
            $totalCares = collect($users)->sum(fn ($user) => Care::type($careType)->count());

            $run = AutomationRun::create([
                'user_id'=>getCurrentUserId(),
                'status' => AutomationStatuses::RUN_PENDING,
                'total_users' => $totalUsers,
                'processed_users' => 0,
                'total_cares' => $totalCares,
                'processed_cares' => 0,
                'input' => [
                    'users'=>$users,
                    'care_type'=>$careType
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

                foreach (Care::type($careType)->get()??[] as $index => $care) {
                    AutomationRunUserCare::create([
                        'automation_run_user_id' => $runUser->id,
                        'care_id'=>$care->id,
                        'sort_order' => $index + 1,
                        'status' => AutomationStatuses::CARE_PENDING,
                        'payload' => $careData['payload'] ?? null,
                    ]);
                }
            }

            return $run;
        });

        ProcessAutomationRunUserJob::dispatch($run);
    }
}
