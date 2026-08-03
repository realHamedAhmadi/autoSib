<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use App\Data\Sib\User\SibUserInfo;
use Morilog\Jalali\Jalalian;

class HyperTensionRiskService extends BaseYoungCareService
{
    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers=[];
        $ids=[10001,10002,10004,12663,18141,18142,18143,19529];
        foreach ($ids as $id){
            $answers[]=[
                "Id_Condition"=>$id,
                "Answer"=>$a=$olderCareDate->getAnswer($id),
                "PostProcessAnswer"=> $a
            ];
        }
        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }
}
