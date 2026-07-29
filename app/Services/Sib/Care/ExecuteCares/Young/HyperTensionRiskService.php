<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use App\Data\Sib\User\SibUserInfo;

class HyperTensionRiskService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        // TODO: Implement alreadyTaken() method.
    }

    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        // TODO: Implement handle() method.
    }
}
