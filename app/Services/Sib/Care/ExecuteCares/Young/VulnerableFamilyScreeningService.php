<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\User\SibUserInfo;
use App\Trails\VulnerableFamilyTrail;

class VulnerableFamilyScreeningService extends BaseYoungCareService
{
    use VulnerableFamilyTrail;

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return $userInfo->birthDate->age>=18 && $userInfo->birthDate->age<30;
    }
}
