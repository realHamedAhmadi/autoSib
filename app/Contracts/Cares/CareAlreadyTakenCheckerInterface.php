<?php

namespace App\Contracts\Cares;

use Illuminate\Support\Carbon;

interface CareAlreadyTakenCheckerInterface
{
    public function alreadyTaken(Carbon $latestVisitDate):bool;
}
