<?php

namespace App\Services\Sib\Care\ExecuteCares;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;
use App\Data\Sib\User\SibUserInfo;

class HyperTensionService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{
    public function hasCare(SibUserInfo $userInfo): bool
    {
        return true;
    }

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        $visitDate=Jalalian::fromDateTime($latestVisitDate);
        $now=Jalalian::now();
        if ($visitDate->getYear()==$now->getYear() && $visitDate->getMonth()==$now->getMonth()){
            return true;
        }
        return false;
    }

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload):array
    {
        $answers=[];
        $answers[]=[
            "answer"=>[
                128980
            ],
            "id_Condition"=>31565
        ];
        $id=10021;
        $sysRand=arrayRandom([120,125,130,135,140]);
        $answers[]=[
            "answer"=> $sysRand,
            "id_Condition"=> $id
        ];

        $id=10112;
        $diasRand=arrayRandom([70,75,80,85,90]);
        $answers[]=[
            "answer"=> $diasRand,
            "id_Condition"=> $id
        ];

        $id=10362;
        $answers[]=[
            "answer"=> $sysRand,
            "id_Condition"=> $id
        ];

        $id=10411;
        $answers[]=[
            "answer"=> $diasRand,
            "id_Condition"=> $id
        ];

        $id=10094;
        $answers[]=[
            "answer"=> $sysRand-arrayRandom([0,5]),
            "id_Condition"=> $id
        ];

        $id=10927;
        $answers[]=[
            "answer"=> $diasRand-arrayRandom([0,5]),
            "id_Condition"=> $id
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

        $id=31554;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=31555;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=31556;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=31557;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=31552;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=11646;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];
        $id=12447;
        $answers[]=[
            "answer"=> 0,
            "id_Condition"=> $id
        ];

        $id=31560;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

        $id=31561;
        $answers[]=[
            "answer"=> 1,
            "id_Condition"=> $id
        ];

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
