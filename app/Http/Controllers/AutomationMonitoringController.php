<?php
namespace App\Http\Controllers;

use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class AutomationMonitoringController extends Controller
{
    /**
     * Render the main dashboard containing run list and details.
     */
    public function index(): View
    {
        $runs = AutomationRun::latest()->paginate(10);
        return view('automation.index', compact('runs'));
    }

    /**
     * Show details of a specific automation run.
     */
    public function show(AutomationRun $run): View
    {
        $run->load(['users' => function($query) {
            $query->orderBy('id', 'asc');
        }]);

        return view('automation.show', compact('run'));
    }

    /**
     * API to fetch the dynamic status of a run (for live polling).
     */
    public function statusApi(AutomationRun $run): JsonResponse
    {
        return response()->json([
            'status' => $run->status,
            'processed_users' => $run->processed_users,
            'total_users' => $run->total_users,
            'processed_cares' => $run->processed_cares,
            'total_cares' => $run->total_cares,
            'user_percentage' => $run->total_users > 0 ? round(($run->processed_users / $run->total_users) * 100) : 0,
            'care_percentage' => $run->total_cares > 0 ? round(($run->processed_cares / $run->total_cares) * 100) : 0,
            'users' => $run->users()->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'sib_user_id' => $user->sib_user_id,
                    'status' => $user->status,
                    'processed_cares' => $user->processed_cares,
                    'total_cares' => $user->total_cares,
                    'progress_percent' => $user->total_cares > 0 ? round(($user->processed_cares / $user->total_cares) * 100) : 0,
                    'error_message' => $user->error_message
                ];
            })
        ]);
    }

    /**
     * API to fetch the detailed list of cares for a specific user.
     */
    public function userCaresApi(AutomationRunUser $user): JsonResponse
    {
        $cares = $user->cares()->with('care')->orderBy('sort_order', 'asc')->get();

        return response()->json([
            'user_name' => $user->payload['name'] ?? ('کاربر ' . $user->sib_user_id),
            'sib_user_id' => $user->sib_user_id,
            'cares' => $cares->map(function($userCare) {
                return [
                    'name' => $userCare->care->title ?? 'مراقبت بدون نام',
                    'status' => $userCare->status,
                    'sort_order' => $userCare->sort_order,
                    'attempts' => $userCare->attempts,
                    'started_at' => $userCare->started_at ? $userCare->started_at->toDateTimeString() : null,
                    'finished_at' => $userCare->finished_at ? $userCare->finished_at->toDateTimeString() : null,
                    'error_message' => $userCare->error_message,
                ];
            })
        ]);
    }
}
