<?php

namespace App\Services\Sib\Care\ExecuteCares\TheElderly;

use App\Services\Sib\Care\ExecuteCares\MiddleAged\BaseMiddleAgedCareService;
use App\Trails\PhysicalActivityCareTrail;

class ElderlyPhysicalActivityService extends BaseMiddleAgedCareService
{
    use PhysicalActivityCareTrail;
}
