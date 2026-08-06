<?php

namespace App\Services\Sib\Care\ExecuteCares\MiddleAged;

use App\Trails\BMITrait;

class MiddleBMIService extends BaseMiddleAgedCareService
{
    use BMITrait;

    protected function addItems(): void
    {
        $this->otherAnswers=[
            23856=>0,
            //30571=>119869,
            10132=>null,
        ];
    }
}
