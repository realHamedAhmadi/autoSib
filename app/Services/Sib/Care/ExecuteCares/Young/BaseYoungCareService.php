<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

abstract class BaseYoungCareService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        return Jalalian::fromCarbon($latestVisitDate)->getYear()==Jalalian::now()->getYear();
    }

    abstract public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array;
}
