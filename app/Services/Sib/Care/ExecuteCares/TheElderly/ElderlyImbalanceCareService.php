<?php

namespace App\Services\Sib\Care\ExecuteCares\TheElderly;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;

class ElderlyImbalanceCareService extends BaseElderlyCareService
{

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers[] =[
            "Id_Condition" => 15195,
            "Answer" => 0,
            "PostProcessAnswer" => 0,
        ];
        $a=$userInfo->birthDate->age>80?1:0;
        $answers[] = [
            "Id_Condition" => 25045,
            "Answer" => $a,
            "PostProcessAnswer" => $a,
        ];
        $answers[] =[
            "Id_Condition" => 25046,
            "Answer" => $a,
            "PostProcessAnswer" => $a,
        ];
        if ($a){
            $answers[] = [
                "Id_Condition" => 33880,
                "Answer" => $a,
                "PostProcessAnswer" => $a,
            ];
        }

        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }
}
