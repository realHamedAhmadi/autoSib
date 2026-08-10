<?php

namespace App\Services\Sib\Care\ExecuteCares;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

class SuspectedAsthmaIdentifierService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        return Jalalian::fromCarbon($latestVisitDate)->getYear()==Jalalian::now()->getYear();
    }

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return true;
    }

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers = [];
        $ids = [33869, 17857, 17856, 30049];
        foreach ($ids as $id){
            $answers[]=[
                "Id_Condition"=>$id,
               "Answer"=> 0,
               "PostProcessAnswer"=> 0
            ];
        }

        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    public function action(int $sibAdminUserId, SibUserInfo $userInfo, ?UserPayload $payload): void
    {
        // TODO: Implement action() method.
    }
}
