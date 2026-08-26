<?php

namespace App\Services\Sib\Care\ExecuteCares\MiddleAged;

use App\Data\Sib\User\SibUserInfo;
use App\Trails\DrugUseCareTrail;

class MiddleDrugUseService extends BaseMiddleAgedCareService
{
    use DrugUseCareTrail;

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return $userInfo->birthDate->age>=30 && $userInfo->birthDate->age<60;
    }
}
