<?php

namespace App\Services\Sib\Care\ExecuteCares\MiddleAged;

use App\Trails\MentalCareTrail;

class MiddleMentalHealthService extends BaseMiddleAgedCareService
{
    use MentalCareTrail;

    protected array $zeroConditions=[19619,19620,16275,28136,23255];
}
