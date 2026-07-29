<?php

namespace App\Services\Sib\Care\ExecuteCares;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;
use App\Data\Sib\User\SibUserInfo;

class DiabeticService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{
    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        $visitDate=Jalalian::fromDateTime($latestVisitDate);
        $now=Jalalian::now();
        if ($visitDate->getYear()==$now->getYear() && $visitDate->getMonth()==$now->getMonth()){
            return true;
        }
        return false;
    }

    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload):array
    {
        $answers=[];
        $answers[]=[
            "answer"=>[
                   116917
            ],
            "id_Condition"=>29765  // fbs checkbox
        ];
        $id=10001; //height
        $answers[]=[
            "answer"=> $olderCareDate->getAnswer($id),
            "id_Condition"=> $id
        ];

        $id=10002; //weight
        $answers[]=[
            "answer"=> $olderCareDate->getAnswer($id),
            "id_Condition"=> $id
        ];

        $id=10004; //bmi
        $answers[]=[
            "answer"=> $olderCareDate->getAnswer($id),
            "id_Condition"=> $id
        ];

        $id=10106; //fbs
        $answers[]=[
            "answer"=> rand(126,137),
            "id_Condition"=> $id
        ];

        $id=10021; //dia
        $answers[]=[
            "answer"=> arrayRandom([120,125,130,135,140]),
            "id_Condition"=> $id
        ];

        $id=10112; //sys
        $answers[]=[
            "answer"=> arrayRandom([70,75,80,85,90]),
            "id_Condition"=> $id
        ];

        $id=29515;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=15149;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=15792;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=29677;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=29678;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=29679;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=29513;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];
        $id=29516;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=25534;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];
        return $answers;
    }
}
