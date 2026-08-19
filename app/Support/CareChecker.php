<?php

namespace App\Support;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Models\AutomationRunUser;
use App\Models\Care;

class CareChecker
{
    protected readonly CareAlreadyTakenCheckerInterface $service;

    public function __construct(
        protected readonly CareType $careType
    )
    {
        $this->service=app()->make(Care::type($this->careType)->firstOrfail()->service->getClassName());
    }

    public function userStatus(string $sibUserId)
    {
        $runUser=AutomationRunUser::query()->where('sib_user_id',$sibUserId)
            ->whereHas('cares.care',function ($q){
                $q->type($this->careType);
            })
            ->latest()->first();
        if (!$runUser || ($runUser->finished_at && !$this->service->alreadyTaken($runUser->finished_at))){
            return 'free';
        }
        return $runUser->status;
    }
}
