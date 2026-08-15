<?php

namespace App\Http\Controllers;

use App\Models\AutomationRun;
use App\Support\AutomationStatuses;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.index', $this->getDashboardData($request));
    }

    public function poll(Request $request): JsonResponse
    {
        $data = $this->getDashboardData($request);

        return response()->json([
            'stats'       => $data['stats'],
            'pendingRuns' => $data['pendingRuns']->map(fn ($run) => $this->transformPendingRun($run))->values(),
            'todayRuns'   => $data['todayRuns']->map(fn ($run) => $this->transformProgressRun($run))->values(),
            'stuckRuns'   => $data['stuckRuns']->map(fn ($run) => $this->transformStuckRun($run))->values(),
            'runs'        => $data['runs']->map(fn ($run) => $this->transformRecentRun($run))->values(),
        ]);
    }

    /**
     * Base query, already scoped to the current user (owners see all).
     */
    private function userScopedRuns(): Builder
    {
        return AutomationRun::query()->forUser(Auth::user());
    }

    private function getDashboardData(Request $request): array
    {
        $stuckMinutes = (int) config('automation.stuck_minutes', 30);
        $today        = Carbon::today();

        $pendingRuns = $this->userScopedRuns()
            ->where('status', 'pending')
            ->latest('id')
            ->get();

        $todayRuns = $this->userScopedRuns()
            ->whereDate('created_at', $today)
            ->latest('id')
            ->get();

        $stuckRuns = $this->userScopedRuns()
            ->where('status', 'running')
            ->where('updated_at', '<=', now()->subMinutes($stuckMinutes))
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $runs = $this->filterRecentRuns($request, $stuckMinutes)->limit(5)->get();

        return [
            'runs'        => $runs,
            'pendingRuns' => $pendingRuns,
            'todayRuns'   => $todayRuns,
            'stuckRuns'   => $stuckRuns,
            'stats'       => $this->calculateStats($stuckMinutes, $today, $pendingRuns, $todayRuns),
        ];
    }

    /**
     * Apply request filters to the "recent runs" list.
     */
    private function filterRecentRuns(Request $request, int $stuckMinutes): Builder
    {
        $query = $this->userScopedRuns()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('care_type')) {
            $query->where('input->care_type', $request->input('care_type'));
        }

        if ($request->boolean('only_stuck')) {
            $query->where('status', 'running')
                ->where('updated_at', '<=', now()->subMinutes($stuckMinutes));
        }

        return $query;
    }

    /**
     * All counters in ONE aggregate query (no N+1).
     */
    private function calculateStats(int $stuckMinutes, Carbon $today, $pendingRuns, $todayRuns): array
    {
        $failedStatuses = [AutomationStatuses::RUN_FAILED, AutomationStatuses::RUN_PARTIAL_FAILED];

        $agg = $this->userScopedRuns()
            ->selectRaw('
                COUNT(*)                                                                 AS total_runs,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END)                              AS running_runs,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END)                              AS done_runs,
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END)                        AS failed_runs,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END)      AS today_runs
            ', [
                'running',
                'done',
                ...$failedStatuses,
                $today->startOfDay()->toDateTimeString(),
                $today->copy()->addDay()->startOfDay()->toDateTimeString(),
            ])->first();

        return [
            'stuck_minutes' => $stuckMinutes,
            'total_runs'    => (int) $agg->total_runs,
            'today_runs'    => (int) $agg->today_runs,
            'pending_runs'  => $pendingRuns->count(),
            'running_runs'  => (int) $agg->running_runs,
            'failed_runs'   => (int) $agg->failed_runs,
            'done_runs'     => (int) $agg->done_runs,
        ];
    }

    // ---- Transformers (unchanged, only formatting) ----

    private function transformPendingRun(AutomationRun $run): array
    {
        return [
            'id'          => $run->id,
            'care_type'   => $run->input['care_type'] ?? '-',
            'total_users' => $run->total_users ?? 0,
            'total_cares' => $run->total_cares ?? 0,
            'created_at'  => $this->jalaliDate($run->created_at),
            'show_url'    => route('automation.runs.show', $run),
        ];
    }

    private function transformProgressRun(AutomationRun $run): array
    {
        return [
            'id'              => $run->id,
            'care_type'       => $run->input['care_type'] ?? '-',
            'status'          => $run->status,
            'processed_users' => $run->processed_users ?? 0,
            'total_users'     => $run->total_users ?? 0,
            'user_percent'    => $this->calculatePercent($run->processed_users, $run->total_users),
            'processed_cares' => $run->processed_cares ?? 0,
            'total_cares'     => $run->total_cares ?? 0,
            'care_percent'    => $this->calculatePercent($run->processed_cares, $run->total_cares),
            'updated_at'      => $this->jalaliDate($run->updated_at, 'Y/m/d H:i:s'),
            'show_url'        => route('automation.runs.show', $run),
        ];
    }

    private function transformStuckRun(AutomationRun $run): array
    {
        return [
            'id'               => $run->id,
            'care_type'        => $run->input['care_type'] ?? '-',
            'updated_at'       => $this->jalaliDate($run->updated_at, 'Y/m/d H:i:s'),
            'inactive_minutes' => $run->updated_at ? now()->diffInMinutes($run->updated_at) : null,
            'show_url'         => route('automation.runs.show', $run),
        ];
    }

    private function transformRecentRun(AutomationRun $run): array
    {
        return [
            'id'              => $run->id,
            'care_type'       => $run->input['care_type'] ?? '-',
            'status'          => $run->status,
            'processed_users' => $run->processed_users ?? 0,
            'total_users'     => $run->total_users ?? 0,
            'user_percent'    => $this->calculatePercent($run->processed_users, $run->total_users),
            'processed_cares' => $run->processed_cares ?? 0,
            'total_cares'     => $run->total_cares ?? 0,
            'care_percent'    => $this->calculatePercent($run->processed_cares, $run->total_cares),
            'created_at'      => $this->jalaliDate($run->created_at),
            'finished_at'     => $this->jalaliDate($run->finished_at),
            'show_url'        => route('automation.runs.show', $run),
        ];
    }

    private function jalaliDate(?Carbon $date, string $format = 'Y/m/d H:i'): string
    {
        return $date ? Jalalian::fromCarbon($date)->format($format) : '-';
    }

    private function calculatePercent(?int $processed, ?int $total): int
    {
        return ($total ?? 0) > 0 ? (int) round((($processed ?? 0) / $total) * 100) : 0;
    }
}
