<?php

namespace App\Trails;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;

trait PhysicalActivityCareTrail
{

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $age=$userInfo->birthDate->age;
        $answers[]=[
            "Id_Condition"=> 34025,
            "Answer"=> $a=$age<70?5:0,
            "PostProcessAnswer"=>$a
        ];

        $answers[]=[
            "Id_Condition"=> 34026,
            "Answer"=> $a=$age<70?30:0,
            "PostProcessAnswer"=>$a
        ];

        $answers[]=[
            "Id_Condition"=> 34027,
            "Answer"=> 0,
            "PostProcessAnswer"=>0
        ];
        $answers[]=[
            "Id_Condition"=> 34029,
            "Answer"=> 0,
            "PostProcessAnswer"=>0
        ];

        return  $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }
}
