<?php

namespace App\Jobs;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Exceptions\CareAlreadyTakenException;
use App\Exceptions\DoesNotHaveCareException;
use App\Exceptions\IgnoreCareException;
use App\Listeners\PendingAutomationRunListener;
use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Models\AutomationRunUserCare;
use App\Models\PendingAutomationRun;
use App\Support\AutomationStatuses;
use App\Services\AutomationProgressService;
use App\Services\Sib\Care\SibCareExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessAutomationRunUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800;

    protected int $sleepTime=15;

    public function __construct(public AutomationRun $run)
    {
    }

    public function handle(
        SibCareExecutor $executor,
        AutomationProgressService $progressService
    ): void {
        Log::info('Job Start....');
        $adminUserId = $this->run->user_id;
        $lockKey = $this->getLockId($adminUserId);
        $runningFlagKey = $this->adminUserRanId($adminUserId);

        $lock = Cache::lock($lockKey, 7500);

        // If the crash-flag exists, it means the previous run crashed without releasing the lock.
        // We force release the lock to allow this new attempt to run.
        if (Cache::has($runningFlagKey)) {
            Log::warning('Recovering from a previous job crash. Force releasing lock.', [
                'user_id' => $adminUserId,
            ]);
            $lock->forceRelease();
        }

        if (! $lock->get()) {
            $this->run->pending()->create([
                'user_id'=>$this->run->user_id,
            ]);
            Log::info('User processing is locked.', [
                'user_id' => $adminUserId,
                'lock_id' => $lockKey,
            ]);
            return;
        }

        // Set the running flag with the same TTL as the lock (7500 seconds)
        Cache::put($runningFlagKey, true, 7500);

        try {
            foreach ($this->run->users()->pluck('id') as $runUserId) {
                $this->handleRunUser(
                    $runUserId,
                    $executor,
                    $progressService
                );
                sleep(20);
            }
        } finally {
            $lock->release();
            Cache::forget($runningFlagKey);
            $this->dispatchPendingRuns($this->run->user_id);
            Log::info('Job finished/cleanup completed.', [
                'total' => $this->run->total_users,
                'processed' => $this->run->processed_users,
            ]);
            $this->run->finished_at=now();
            $this->run->save();
        }
    }


    function handleRunUser(
        int $runUserId,
        SibCareExecutor $executor,
        AutomationProgressService $progressService
    ) {

        /** @var AutomationRunUser|null $runUser */
        $runUser = AutomationRunUser::with(['run', 'cares'])->find($runUserId);

        if (! $runUser) {
            return;
        }

        if (! in_array($runUser->status, [
            AutomationStatuses::USER_PENDING,
            AutomationStatuses::USER_RUNNING,
            AutomationStatuses::USER_PAUSED,
            AutomationStatuses::USER_FAILED,
        ], true)) {
            return;
        }

        if ($runUser->run->status === AutomationStatuses::RUN_PENDING) {
            $runUser->run->update([
                'status' => AutomationStatuses::RUN_RUNNING,
                'started_at' => $runUser->run->started_at ?? now(),
            ]);
        }

        $runUser->update([
            'status' => AutomationStatuses::USER_RUNNING,
            'started_at' => $runUser->started_at ?? now(),
            'last_attempt_at' => now(),
            'error_message' => null,
        ]);

        $cares = $runUser->cares;

        foreach ($cares as $care) {
            if ($care->status === AutomationStatuses::CARE_DONE || $care->status === AutomationStatuses::CARE_SKIPPED) {
                continue;
            }
            $runUser->update([
                'current_care_id' => $care->id,
            ]);

            $care->update([
                'status' => AutomationStatuses::CARE_RUNNING,
                'attempts' => $care->attempts + 1,
                'started_at' => $care->started_at ?? now(),
                'error_message' => null,
            ]);

            try {
                $adminUserId=$runUser->run->user_id;

                $careService=app()->make($care->care->service->getClassName());
                if (!$careService instanceof CareAlreadyTakenCheckerInterface){
                    throw new \LogicException(sprintf(
                        'Care service must implement %s, %s given.',
                        CareAlreadyTakenCheckerInterface::class,
                        $careService::class
                    ));
                }
                $latestCare=AutomationRunUserCare::query()
                    ->where('status',AutomationStatuses::CARE_DONE)
                    ->whereHas('runUser',function ($q)use($runUser){
                        $q->where('sib_user_id',$runUser->sib_user_id);
                    })->whereHas('runUser.run',function ($q)use ($adminUserId){
                        $q->where('user_id',$adminUserId);
                    })->whereHas('care',function ($q)use($care){
                        $q->where('care_id',$care->care_id);
                    })->latest()->first();
                if ($latestCare?->finished_at && $careService->alreadyTaken(Carbon::parse($latestCare?->finished_at))){
                    throw new CareAlreadyTakenException();
                }
                $executor->execute(
                    adminUserId:$adminUserId,
                    sibUserId: $runUser->sib_user_id,
                    sibCareId: $care->care_id,
                    payload:$runUser->payload
                );

                $care->update([
                    'status' => AutomationStatuses::CARE_DONE,
                    'finished_at' => now(),
                    'error_message' => null,
                ]);
            }catch (Throwable $e) {
                if ($e instanceof CareAlreadyTakenException
                    || $e instanceof IgnoreCareException
                    || $e instanceof DoesNotHaveCareException
                ){
                    $status=AutomationStatuses::CARE_SKIPPED;
                }else {
                    $status=AutomationStatuses::CARE_FAILED;
                    Log::error('Care execution failed in ProcessAutomationRunUserJob', [
                        'run_id' => $runUser->automation_run_id,
                        'run_user_id' => $runUser->id,
                        'sib_user_id' => $runUser->sib_user_id,
                        'care_id' => $care->id,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }

                if (Str::contains($e->getMessage(),'صبر کرده و سپس مجددا تلاش کنید')){
                    $this->sleepTime+=10;
                }

                $care->update([
                    'status' => $status,
                    'error_message' => $e->getMessage(),
                    'result' => [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                    ],
                    'finished_at' => now(),
                ]);
                $progressService->refreshRunUser($runUser->id);
                $progressService->refreshRun($runUser->automation_run_id);
                sleep($this->sleepTime);
                continue;
            }

            $progressService->refreshRunUser($runUser->id);
            $progressService->refreshRun($runUser->automation_run_id);
            sleep($this->sleepTime);
        }

        $runUser->update([
            'status' => AutomationStatuses::USER_DONE,
            'finished_at' => now(),
            'error_message' => null,
        ]);

        $progressService->refreshRunUser($runUser->id);
        $progressService->refreshRun($runUser->automation_run_id);

    }

    protected function getLockId(int $adminUserid)
    {
        return "automation_run_lock_$adminUserid";
    }

    protected function adminUserRanId(int $adminUserid)
    {
        return "run_admin_user_$adminUserid";
    }

    protected function dispatchPendingRuns($userId):void
    {
        $pendingRuns=PendingAutomationRun::where('user_id',$userId)->get();
        foreach($pendingRuns as $pending){
            ProcessAutomationRunUserJob::dispatch($pending->run);
            $pending->delete();
        }
    }

    public function failed(Throwable $e):void
    {
        $this->run->update([
            'status'=>AutomationStatuses::RUN_FAILED,
            'finished_at'=>now()
        ]);
        $runUser=$this->run->users()->where('status',AutomationStatuses::USER_RUNNING)->first();
        $runUser->update([
            'status'=>AutomationStatuses::USER_FAILED,
            'finished_at'=>now()
        ]);
        $runUser->cares()
            ->where('status',AutomationStatuses::CARE_RUNNING)
            ->update([
                'status'=>AutomationStatuses::CARE_FAILED,
                'finished_at'=>now()
            ]);
        $this->dispatchPendingRuns($this->run->user_id);
    }
}
