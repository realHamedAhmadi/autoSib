<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\User\SibUserInfo;
use App\Trails\MentalCareTrail;

class MentalHealthService extends BaseYoungCareService
{
    use MentalCareTrail;

    // Conditions that always return 0 (Not applicable/No)
    protected array $zeroConditions = [16275, 28136, 23132, 16258, 16259, 23967];

    public function hasCare(SibUserInfo $userInfo): bool
    {
        return $userInfo->birthDate->age>=18 && $userInfo->birthDate->age<30;
    }
}
