<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;
use App\Data\Sib\User\SibUserInfo;

class PhysicalActivityService implements CareHandlerInterface,CareAlreadyTakenCheckerInterface
{

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        return Jalalian::fromCarbon($latestVisitDate)->getYear()==Jalalian::now()->getYear();
    }

    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers[]=[
            "Id_Condition"=> 34025,
            "Answer"=> 5,
            "PostProcessAnswer"=>5
        ];

        $answers[]=[
            "Id_Condition"=> 34026,
            "Answer"=> 30,
            "PostProcessAnswer"=>30
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
}
