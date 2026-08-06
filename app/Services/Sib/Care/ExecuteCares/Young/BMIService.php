<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Trails\BMITrait;

class BMIService extends BaseYoungCareService
{
    use BMITrait;
    protected function addItems(): void
    {
        $this->otherAnswers=[
            25668=>0,
        ];
        $this->threeOptionAnswers=array_merge(
            $this->threeOptionAnswers,
            [
                // Physical activity: none, below 150 minutes, 150+ minutes weekly.
                16656 => [
                    100428 => 10,
                    100429 => 20,
                    100430 => 70,
                ],
            ]
        );
    }
}
