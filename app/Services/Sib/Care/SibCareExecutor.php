<?php

namespace App\Services\Sib\Care;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Exceptions\CareAlreadyTakenException;
use App\Exceptions\DoesNotHaveCareException;
use App\Models\Care;
use App\Services\Sib\User\SibUserService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SibCareExecutor
{
    /**
     * Dependency injection via constructor parameters.
     */
    public function __construct(
        protected readonly SibUserService $sibUserService,
        protected readonly SibCareService $sibCareService,
        protected readonly SibChildCareIndexService $childCareIndexService
    ) {}

    /**
     * @throws BindingResolutionException
     * @throws RuntimeException
     */
    public function execute(string $adminUserId, string $sibUserId, string $sibCareId, array $payload): void
    {
        $care = Care::findOrFail($sibCareId);
        Log::info($care->title);
        $careService=app()->make($care->service->getClassName());
        $userInfo = $this->sibUserService->getInfoByNationalId($sibUserId, $adminUserId);

        $token = $this->sibUserService->selectUser($sibUserId, $userInfo->userToken,$adminUserId);

        setCurrentUserToken($token, $adminUserId);

        $userInfo = $this->sibUserService->getInfoByToken($userInfo->userToken, $adminUserId);
        if (!$careService->hasCare($userInfo)){
            throw new DoesNotHaveCareException();
        }
        $olderData = $this->getOlderData($adminUserId, $userInfo, $care);

        $careIndexItem = $this->childCareIndexService->getHashFrom($care->code, $adminUserId);

        if ($careIndexItem?->dateVisit && $careService->alreadyTaken($careIndexItem->dateVisit)){
            throw new CareAlreadyTakenException();
        }

        $hash = $this->sibCareService->saveFrom($care->code, $careIndexItem->hash,null,null,$adminUserId);
        $answers = $careService->firstForm($olderData,$userInfo,UserPayload::fromArray($payload));
        $hash = $this->sibCareService->saveFrom($care->code, $careIndexItem->hash, $hash, $answers,$adminUserId);
        $answers=$careService->secondForm($olderData,$userInfo,UserPayload::fromArray($payload));
        if (!empty($answers)){
            $hash = $this->sibCareService->saveFrom($care->code, $careIndexItem->hash, $hash, $answers,$adminUserId);
        }
        $this->sibCareService->saveFrom($care->code, $careIndexItem->hash, $hash, null,$adminUserId);
    }

    protected function getOlderData(string $adminUserId, SibUserInfo $userInfo, Care $care): CompletedCareData
    {
        $visits = $this->sibCareService->listOfCompleted($userInfo->userToken, $adminUserId);

        foreach ($visits as $visit) {
            if ($visit->idChildIndex == $care->code) {
                return $this->sibCareService->completedCareData($visit->basicVisitToken->token, $adminUserId);
            }
        }

        return CompletedCareData::fromApiResponse([]);
    }
}
