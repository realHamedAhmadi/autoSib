<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\User\SibUserInfo;
use App\Trails\DrugUseCareTrail;

class DrugUseService extends BaseYoungCareService
{
  use DrugUseCareTrail;

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return $userInfo->birthDate->age>=18 && $userInfo->birthDate->age<30;
    }
}
