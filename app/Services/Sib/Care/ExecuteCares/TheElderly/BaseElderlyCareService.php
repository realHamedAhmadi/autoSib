<?php

namespace App\Services\Sib\Care\ExecuteCares\TheElderly;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

abstract class BaseElderlyCareService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return true;
    }

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        return Jalalian::fromCarbon($latestVisitDate)->getYear()==Jalalian::now()->getYear();
    }

    abstract public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array;
    abstract public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array;
}
