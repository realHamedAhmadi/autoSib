<?php

namespace App\Http\Controllers;

use App\Models\AutomationRun;
use App\Support\AutomationStatuses;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $dashboardData = $this->getDashboardData($request);

        return view('dashboard.index', $dashboardData);
    }

    /**
     * Return live dashboard data for AJAX polling.
     */
    public function poll(Request $request): JsonResponse
    {
        $dashboardData = $this->getDashboardData($request);

        return response()->json([
            'stats' => $dashboardData['stats'],

            'pendingRuns' => $dashboardData['pendingRuns']
                ->map(fn (AutomationRun $run) => $this->transformPendingRun($run))
                ->values(),

            'todayRuns' => $dashboardData['todayRuns']
                ->map(fn (AutomationRun $run) => $this->transformProgressRun($run))
                ->values(),

            'stuckRuns' => $dashboardData['stuckRuns']
                ->map(fn (AutomationRun $run) => $this->transformStuckRun($run))
                ->values(),

            'runs' => $dashboardData['runs']
                ->map(fn (AutomationRun $run) => $this->transformRecentRun($run))
                ->values(),
        ]);
    }

    /**
     * Retrieve all dashboard sections and counters.
     */
    private function getDashboardData(Request $request): array
    {
        $authUser = Auth::user();
        $stuckMinutes = (int) config('automation.stuck_minutes', 30);
        $today = Carbon::today();

        $pendingRuns = AutomationRun::query()
            ->when(
                ! $authUser->isOwner(),
                function ($query) use ($authUser) {
                    $query->where(function ($query) use ($authUser) {
                        $query->where('user_id', $authUser->id);
                    });
                }
            )
            ->where('status', 'pending')
            ->latest('id')
            ->get();

        $todayRuns = AutomationRun::query()
            ->when(
                ! $authUser->isOwner(),
                function ($query) use ($authUser) {
                    $query->where(function ($query) use ($authUser) {
                        $query->where('user_id', $authUser->id);
                    });
                }
            )
            ->whereDate('created_at', $today)
            ->latest('id')
            ->get();

        $stuckRuns = AutomationRun::query()
            ->when(
                ! $authUser->isOwner(),
                function ($query) use ($authUser) {
                    $query->where(function ($query) use ($authUser) {
                        $query->where('user_id', $authUser->id);
                    });
                }
            )
            ->where('status', 'running')
            ->where('updated_at', '<=', now()->subMinutes($stuckMinutes))
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $recentRunsQuery = AutomationRun::query()
            ->when(
                ! $authUser->isOwner(),
                function ($query) use ($authUser) {
                    $query->where(function ($query) use ($authUser) {
                        $query->where('user_id', $authUser->id);
                    });
                }
            )
            ->latest('id');

        if ($request->filled('status')) {
            $recentRunsQuery->where('status', $request->input('status'));
        }

        if ($request->filled('care_type')) {
            $recentRunsQuery->where('input->care_type', $request->input('care_type'));
        }

        if ($request->boolean('only_stuck')) {
            $recentRunsQuery
                ->where('status', 'running')
                ->where('updated_at', '<=', now()->subMinutes($stuckMinutes));
        }

        $runs = $recentRunsQuery
            ->limit(5)
            ->get();

        return [
            'runs' => $runs,
            'pendingRuns' => $pendingRuns,
            'todayRuns' => $todayRuns,
            'stuckRuns' => $stuckRuns,
            'stats' => [
                'stuck_minutes' => $stuckMinutes,
                'total_runs' => AutomationRun::count(),
                'today_runs' => $todayRuns->count(),
                'pending_runs' => $pendingRuns->count(),
                'running_runs' => AutomationRun::query()
                    ->where('status', 'running')
                    ->count(),
                'failed_runs' => AutomationRun::query()
                    ->where('status', AutomationStatuses::RUN_FAILED)
                    ->orWhere('status', AutomationStatuses::RUN_PARTIAL_FAILED)
                    ->count(),
                'done_runs' => AutomationRun::query()
                    ->where('status', 'done')
                    ->count(),
            ],
        ];
    }

    /**
     * Transform a pending run for JSON response.
     */
    private function transformPendingRun(AutomationRun $run): array
    {
        return [
            'id' => $run->id,
            'care_type' => $run->input['care_type'] ?? '-',
            'total_users' => $run->total_users ?? 0,
            'total_cares' => $run->total_cares ?? 0,
            'created_at' => $this->jalaliDate($run->created_at),
            'show_url' => route('automation.runs.show', $run),
        ];
    }

    /**
     * Transform a run with progress information for JSON response.
     */
    private function transformProgressRun(AutomationRun $run): array
    {
        return [
            'id' => $run->id,
            'care_type' => $run->input['care_type'] ?? '-',
            'status' => $run->status,
            'processed_users' => $run->processed_users ?? 0,
            'total_users' => $run->total_users ?? 0,
            'user_percent' => $this->calculatePercent(
                $run->processed_users,
                $run->total_users
            ),
            'processed_cares' => $run->processed_cares ?? 0,
            'total_cares' => $run->total_cares ?? 0,
            'care_percent' => $this->calculatePercent(
                $run->processed_cares,
                $run->total_cares
            ),
            'updated_at' => $this->jalaliDate($run->updated_at, 'Y/m/d H:i:s'),
            'show_url' => route('automation.runs.show', $run),
        ];
    }

    /**
     * Transform a stuck run for JSON response.
     */
    private function transformStuckRun(AutomationRun $run): array
    {
        return [
            'id' => $run->id,
            'care_type' => $run->input['care_type'] ?? '-',
            'updated_at' => $this->jalaliDate($run->updated_at, 'Y/m/d H:i:s'),
            'inactive_minutes' => $run->updated_at
                ? now()->diffInMinutes($run->updated_at)
                : null,
            'show_url' => route('automation.runs.show', $run),
        ];
    }

    /**
     * Transform a recent run for JSON response.
     */
    private function transformRecentRun(AutomationRun $run): array
    {
        return [
            'id' => $run->id,
            'care_type' => $run->input['care_type'] ?? '-',
            'status' => $run->status,
            'processed_users' => $run->processed_users ?? 0,
            'total_users' => $run->total_users ?? 0,
            'user_percent' => $this->calculatePercent(
                $run->processed_users,
                $run->total_users
            ),
            'processed_cares' => $run->processed_cares ?? 0,
            'total_cares' => $run->total_cares ?? 0,
            'care_percent' => $this->calculatePercent(
                $run->processed_cares,
                $run->total_cares
            ),
            'created_at' => $this->jalaliDate($run->created_at),
            'finished_at' => $this->jalaliDate($run->finished_at),
            'show_url' => route('automation.runs.show', $run),
        ];
    }

    /**
     * Convert Carbon date to Jalali date.
     */
    private function jalaliDate(?Carbon $date, string $format = 'Y/m/d H:i'): string
    {
        if ($date === null) {
            return '-';
        }

        return Jalalian::fromCarbon($date)->format($format);
    }

    /**
     * Calculate a safe percentage value.
     */
    private function calculatePercent(?int $processed, ?int $total): int
    {
        if (($total ?? 0) <= 0) {
            return 0;
        }

        return (int) round((($processed ?? 0) / $total) * 100);
    }
}
