<?php

namespace App\Trails;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Support\MaritalStatus;

trait DrugUseCareTrail
{
    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers[]=[
            "Id_Condition"=>29736,
            "Answer"=>1,
            "PostProcessAnswer"=> 1
        ];
        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers=[];
        $ids=[
            29480=>115401,
            29481=>115404,
            29482=>115407,
            29483=>115410,
            29484=>115413,
            29485=>115416,
            29486=>115419,
            28136=>0,
        ];
        if($userInfo->maritalStatus==MaritalStatus::MARRIED->value){
            $ids[29565]=1;
            $ids[29568]=0;
            $ids[29569]=0;
            $ids[29570]=0;
            $ids[29571]=0;
            $ids[29576]=0;
            $ids[29574]=0;
        }
        foreach ($ids as $id=>$ans){
            $answers[]=[
                "Id_Condition"=>$id,
                "Answer"=>$ans,
                "PostProcessAnswer"=> $ans
            ];
        }
        return $answers;
    }
}
