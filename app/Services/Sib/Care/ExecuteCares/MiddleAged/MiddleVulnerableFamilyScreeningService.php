<?php

namespace App\Services\Sib\Care\ExecuteCares\MiddleAged;

use App\Data\Sib\User\SibUserInfo;
use App\Support\GenderStatus;
use App\Support\MaritalStatus;
use App\Trails\VulnerableFamilyTrail;
use Illuminate\Support\Facades\Log;

class MiddleVulnerableFamilyScreeningService extends BaseMiddleAgedCareService
{
    use VulnerableFamilyTrail;
    public function hasCare(SibUserInfo $userInfo): bool
    {
        return ($userInfo->gender==GenderStatus::WOMAN->value && $userInfo->maritalStatus==MaritalStatus::MARRIED->value)
               && ($userInfo->birthDate->age>=30 && $userInfo->birthDate->age<60);
    }
}
