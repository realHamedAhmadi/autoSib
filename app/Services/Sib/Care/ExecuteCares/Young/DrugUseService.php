<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use App\Data\Sib\User\SibUserInfo;

class DrugUseService extends BaseYoungCareService
{
    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $answers=[];
        $answers[]=[
            "Id_Condition"=>29736,
            "Answer"=>1,
            "PostProcessAnswer"=> 1
        ];
        $ids=[
            29736=>1,
            29480=>115401,
            29481=>115404,
            29482=>115407,
            29483=>115410,
            29484=>115413,
            29485=>115416,
            29486=>115419,
            28136=>0,
            29565=>0,
            ];
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
