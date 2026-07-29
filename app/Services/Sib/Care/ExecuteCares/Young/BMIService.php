<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use Illuminate\Support\Carbon;
use App\Data\Sib\User\SibUserInfo;

class BMIService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        // TODO: Implement alreadyTaken() method.
    }

    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?array $payload): array
    {
        // TODO: Implement handle() method.
    }
}
