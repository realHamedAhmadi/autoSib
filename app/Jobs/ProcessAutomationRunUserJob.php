<?php

namespace App\Jobs;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Exceptions\CareAlreadyTakenException;
use App\Exceptions\DoesNotHaveCareException;
use App\Exceptions\IgnoreCareException;
use App\Models\AutomationRun;
use App\Models\AutomationRunUser;
use App\Models\AutomationRunUserCare;
use App\Models\PendingAutomationRun;
use App\Services\AutomationProgressService;
use App\Services\Sib\Care\SibCareService;
use App\Services\Sib\Care\SibChildCareIndexService;
use App\Services\Sib\User\SibUserService;
use App\Support\AutomationStatuses;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

class ProcessAutomationRunUserJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const LOCK_TTL = 7500;

    /**
     * The number of seconds between care executions.
     */
    private int $sleepTime = 15;

    private bool $isSlept=true;

    public int $tries = 1;

    public int $timeout = 1800;
    protected readonly SibUserService $sibUserService;
    protected readonly SibCareService $sibCareService;
    protected readonly SibChildCareIndexService $childCareIndexService;
    protected readonly AutomationProgressService $progressService;

    public function __construct(
        protected readonly AutomationRun $run,
    ) {
    }

    public function handle(
        SibUserService $sibUserService,
        SibCareService $sibCareService,
        SibChildCareIndexService $childCareIndexService,
        AutomationProgressService $progressService,
    ): void
    {
        $this->sibUserService=$sibUserService;
        $this->sibCareService=$sibCareService;
        $this->childCareIndexService=$childCareIndexService;
        $this->progressService=$progressService;

        Log::info('Automation run job started.', [
            'run_id' => $this->run->id,
            'user_id' => $this->run->user_id,
        ]);

        $adminUserId = (int) $this->run->user_id;
        $lock = Cache::lock($this->lockKey($adminUserId), self::LOCK_TTL);
        $runningFlagKey = $this->runningFlagKey($adminUserId);

        if (! $lock->get()) {
            $this->queueAsPending($adminUserId);

            Log::info('Automation run is locked.', [
                'run_id' => $this->run->id,
                'user_id' => $adminUserId,
            ]);

            return;
        }

        Cache::put($runningFlagKey, true, self::LOCK_TTL);

        try {
            $this->processRunUsers();
            $this->markRunAsFinished();
        } finally {
            $lock->release();
            Cache::forget($runningFlagKey);

            $this->dispatchPendingRuns($adminUserId);

            Log::info('Automation run job cleanup completed.', [
                'run_id' => $this->run->id,
                'total_users' => $this->run->total_users,
                'processed_users' => $this->run->processed_users,
            ]);
        }
    }

    private function processRunUsers(): void
    {
        $runUsers = $this->run
            ->users()
            ->with(['run', 'cares'])
            ->get();

        foreach ($runUsers as $runUser) {
            if ($this->shouldSkipRunUser($runUser)) {
                continue;
            }

            $this->processRunUser($runUser);

            sleep(20);
        }
    }

    private function processRunUser(AutomationRunUser $runUser): void
    {
        if (! $this->isProcessableUserStatus($runUser->status)) {
            return;
        }

        $this->markRunAsRunning($runUser);
        $this->markRunUserAsRunning($runUser);

        $adminUserId = (int) $runUser->run->user_id;
        $sibUserId = (int) $runUser->sib_user_id;
        $payload = UserPayload::fromArray($runUser->payload);

        $userInfo = $this->sibUserService
            ->getInfoByNationalId($sibUserId, $adminUserId);

        $token = $this->sibUserService->selectUser(
            $sibUserId,
            $userInfo->userToken,
            $adminUserId,
        );

        setCurrentUserToken($token, $adminUserId);

        $userInfo = $this->sibUserService->getFullInfo(
            $userInfo->userToken,
            $adminUserId,
        );

        $completedVisits = $this->sibCareService->listOfCompleted(
            $userInfo->userToken,
            $adminUserId,
        );

        foreach ($runUser->cares as $runUserCare) {
            if ($this->shouldSkipCare($runUserCare)) {
                continue;
            }

            $this->processCare(
                $runUser,
                $runUserCare,
                $userInfo,
                $completedVisits,
                $payload,
                $adminUserId,
            );

            $this->refreshProgress($runUser);
            if ($this->isSlept){
                sleep($this->sleepTime);
            }
            $this->isSlept=true;
        }

        $this->markRunUserAsDone($runUser);
        $this->refreshProgress($runUser);
    }

    private function processCare(
        AutomationRunUser $runUser,
        AutomationRunUserCare $runUserCare,
        SibUserInfo $userInfo,
        iterable $completedVisits,
        UserPayload $payload,
        int $adminUserId,
    ): void {
        $runUser->update([
            'current_care_id' => $runUserCare->id,
        ]);

        $this->markCareAsRunning($runUserCare);

        try {
            $care = $runUserCare->care;
            if (!$care){
                return;
            }
            $careService = app()->make($care->service->getClassName());

            $this->assertValidCareService($careService);

            $completedCareData = $this->getCompletedCareData(
                $completedVisits,
                $care->code,
                $adminUserId,
            );

            $this->assertCareIsAvailable(
                $careService,
                $userInfo,
                $runUser,
                $runUserCare,
                $adminUserId,
            );

            $this->executeCare(
                $careService,
                $care->code,
                $completedCareData,
                $userInfo,
                $payload,
                $adminUserId,
            );

            $this->markCareAsDone($runUserCare);
        } catch (Throwable $exception) {
            $this->handleCareFailure($runUser, $runUserCare, $exception);
        }
    }

    private function executeCare(
        CareAlreadyTakenCheckerInterface $careService,
        string $careCode,
        CompletedCareData $completedCareData,
        SibUserInfo $userInfo,
        UserPayload $payload,
        int $adminUserId,
    ): void {
        $careIndexItem = $this->childCareIndexService->getHashFrom(
            $careCode,
            $adminUserId,
        );

        if (! $careIndexItem?->hash) {
            throw new LogicException(
                "Care index hash was not found for care code [{$careCode}]."
            );
        }

        $hash = $this->sibCareService->saveFrom(
            $careCode,
            $careIndexItem->hash,
            null,
            null,
            $adminUserId,
        );

        $answers = $careService->firstForm(
            $completedCareData,
            $userInfo,
            $payload,
        );
        Log::info($answers);

        $hash = $this->sibCareService->saveFrom(
            $careCode,
            $careIndexItem->hash,
            $hash,
            $answers,
            $adminUserId,
        );

        $answers = $careService->secondForm(
            $completedCareData,
            $userInfo,
            $payload,
        );

        if (! empty($answers)) {
            $hash = $this->sibCareService->saveFrom(
                $careCode,
                $careIndexItem->hash,
                $hash,
                $answers,
                $adminUserId,
            );
        }

        $this->sibCareService->saveFrom(
            $careCode,
            $careIndexItem->hash,
            $hash,
            null,
            $adminUserId,
        );

        $careService->action($adminUserId, $userInfo, $payload);
    }

    private function assertCareIsAvailable(
        CareAlreadyTakenCheckerInterface $careService,
        object $userInfo,
        AutomationRunUser $runUser,
        AutomationRunUserCare $runUserCare,
        int $adminUserId,
    ): void {
        if (! $careService->hasCare($userInfo)) {
            throw new DoesNotHaveCareException();
        }

        $latestCare = $this->findLatestCompletedCare(
            $runUser,
            $runUserCare,
            $adminUserId,
        );

        if (
            $latestCare?->finished_at
            && $careService->alreadyTaken(
                Carbon::parse($latestCare->finished_at)
            )
        ) {
            throw new CareAlreadyTakenException();
        }

        $careIndexItem = $this->childCareIndexService->getHashFrom(
            $runUserCare->care->code,
            $adminUserId,
        );

        if (
            $careIndexItem?->dateVisit
            && $careService->alreadyTaken($careIndexItem->dateVisit)
        ) {
            throw new CareAlreadyTakenException();
        }
    }

    private function getCompletedCareData(
        iterable $completedVisits,
        string $careCode,
        int $adminUserId,
    ): CompletedCareData {
        foreach ($completedVisits as $visit) {
            if ((string)$visit->idChildIndex === $careCode) {
                return $this->sibCareService->completedCareData(
                    $visit->basicVisitToken->token,
                    $adminUserId,
                );
            }
        }

        return CompletedCareData::fromApiResponse([]);
    }

    private function findLatestCompletedCare(
        AutomationRunUser $runUser,
        AutomationRunUserCare $runUserCare,
        int $adminUserId,
    ): ?AutomationRunUserCare {
        return AutomationRunUserCare::query()
            ->where('status', AutomationStatuses::CARE_DONE)
            ->whereHas('runUser', function ($query) use ($runUser): void {
                $query->where('sib_user_id', $runUser->sib_user_id);
            })
            ->whereHas('runUser.run', function ($query) use ($adminUserId): void {
                $query->where('user_id', $adminUserId);
            })
            ->where('care_id', $runUserCare->care_id)
            ->latest('finished_at')
            ->first();
    }

    private function handleCareFailure(
        AutomationRunUser $runUser,
        AutomationRunUserCare $runUserCare,
        Throwable $exception,
    ): void {
        $isSkippable = $exception instanceof CareAlreadyTakenException
            || $exception instanceof IgnoreCareException
            || $exception instanceof DoesNotHaveCareException;
        $this->isSlept=!$isSkippable;
        $status = $isSkippable
            ? AutomationStatuses::CARE_SKIPPED
            : AutomationStatuses::CARE_FAILED;

        if (! $isSkippable) {
            Log::error('Care execution failed.', [
                'run_id' => $runUser->automation_run_id,
                'run_user_id' => $runUser->id,
                'sib_user_id' => $runUser->sib_user_id,
                'care_id' => $runUserCare->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        if (Str::contains(
            $exception->getMessage(),
            'صبر کرده و سپس مجددا تلاش کنید'
        )) {
            $this->sleepTime += 10;
        }

        $runUserCare->update([
            'status' => $status,
            'error_message' => $exception->getMessage(),
            'result' => [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ],
            'finished_at' => now(),
        ]);
    }

    private function assertValidCareService(object $careService): void
    {
        if (! $careService instanceof CareAlreadyTakenCheckerInterface) {
            throw new LogicException(sprintf(
                'Care service must implement %s, %s given.',
                CareAlreadyTakenCheckerInterface::class,
                $careService::class,
            ));
        }
    }

    private function markRunAsRunning(AutomationRunUser $runUser): void
    {
        if ($runUser->run->status === AutomationStatuses::RUN_PENDING) {
            $runUser->run->update([
                'status' => AutomationStatuses::RUN_RUNNING,
                'started_at' => $runUser->run->started_at ?? now(),
            ]);
        }
    }

    private function markRunUserAsRunning(AutomationRunUser $runUser): void
    {
        $runUser->update([
            'status' => AutomationStatuses::USER_RUNNING,
            'started_at' => $runUser->started_at ?? now(),
            'last_attempt_at' => now(),
            'error_message' => null,
        ]);
    }

    private function markCareAsRunning(AutomationRunUserCare $runUserCare): void
    {
        $runUserCare->update([
            'status' => AutomationStatuses::CARE_RUNNING,
            'attempts' => $runUserCare->attempts + 1,
            'started_at' => $runUserCare->started_at ?? now(),
            'error_message' => null,
        ]);
    }

    private function markCareAsDone(AutomationRunUserCare $runUserCare): void
    {
        $runUserCare->update([
            'status' => AutomationStatuses::CARE_DONE,
            'finished_at' => now(),
            'error_message' => null,
        ]);
    }

    private function markRunUserAsDone(AutomationRunUser $runUser): void
    {
        $runUser->update([
            'status' => AutomationStatuses::USER_DONE,
            'finished_at' => now(),
            'error_message' => null,
        ]);
    }

    private function markRunAsFinished(): void
    {
        $this->run->update([
            'finished_at' => now(),
        ]);
    }

    private function refreshProgress(AutomationRunUser $runUser): void
    {
        $this->progressService->refreshRunUser($runUser->id);
        $this->progressService->refreshRun($runUser->automation_run_id);
    }

    private function shouldSkipRunUser(AutomationRunUser $runUser): bool
    {
        return $runUser->status === AutomationStatuses::USER_DONE;
    }

    private function shouldSkipCare(AutomationRunUserCare $runUserCare): bool
    {
        return in_array($runUserCare->status, [
            AutomationStatuses::CARE_DONE,
            AutomationStatuses::CARE_SKIPPED,
        ], true);
    }

    private function isProcessableUserStatus(string $status): bool
    {
        return in_array($status, [
            AutomationStatuses::USER_PENDING,
            AutomationStatuses::USER_RUNNING,
            AutomationStatuses::USER_PAUSED,
            AutomationStatuses::USER_FAILED,
        ], true);
    }

    private function queueAsPending(int $adminUserId): void
    {
        PendingAutomationRun::firstOrCreate([
            'user_id' => $adminUserId,
            'run_id' => $this->run->id,
        ]);
    }

    private function dispatchPendingRuns(int $userId): void
    {
        PendingAutomationRun::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->each(function (PendingAutomationRun $pendingRun): void {
                self::dispatch($pendingRun->run);
                $pendingRun->delete();
            });
    }

    private function lockKey(int $adminUserId): string
    {
        return "automation_run_lock_{$adminUserId}";
    }

    private function runningFlagKey(int $adminUserId): string
    {
        return "automation_run_running_{$adminUserId}";
    }

    public function failed(Throwable $exception): void
    {
        $this->run->update([
            'status' => AutomationStatuses::RUN_FAILED,
            'finished_at' => now(),
        ]);

        $runUser = $this->run
            ->users()
            ->where('status', AutomationStatuses::USER_RUNNING)
            ->first();

        if (! $runUser) {
            $this->dispatchPendingRuns((int) $this->run->user_id);

            return;
        }

        $runUser->update([
            'status' => AutomationStatuses::USER_FAILED,
            'finished_at' => now(),
        ]);

        $runUser->cares()
            ->where('status', AutomationStatuses::CARE_RUNNING)
            ->update([
                'status' => AutomationStatuses::CARE_FAILED,
                'finished_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

        $this->dispatchPendingRuns((int) $this->run->user_id);
    }
}
