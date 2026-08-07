<?php

namespace App\Services\Sib\Care\ExecuteCares\TheElderly;

use App\Services\Sib\Care\ExecuteCares\Young\BaseYoungCareService;
use App\Trails\BMITrait;

class ElderlyBMIService extends BaseYoungCareService
{
    use BMITrait;
    protected function addItems(): void
    {
        $this->otherAnswers=[
            // Diseases checklist: diabetes / hypertension / dyslipidemia / none.
            30093=>[118385],
        ];
        $this->threeOptionAnswers=[
            // Fruit consumption: rarely / less than 2 servings / 2 to 4 servings.
            23049 => [
                107469 => 10,
                107470 => 20,
                107471 => 70,
            ],

            // Vegetable consumption: rarely / less than 3 servings / 3 to 5 servings.
            23050 => [
                107472 => 10,
                107473 => 20,
                107474 => 70,
            ],

            // Dairy consumption: rarely / less than 2 servings / 2 or more servings.
            23051 => [
                107475 => 10,
                107476 => 20,
                107477 => 70,
            ],

            // Salt shaker use: always / sometimes / rarely or never.
            23052 => [
                107478 => 10,
                107479 => 20,
                107480 => 70,
            ],

            // Fast food / soda consumption: 2+ times a week / 1-2 times a month / rarely or never.
            23054 => [
                107484 => 10,
                107485 => 20,
                107486 => 70,
            ],

            // Oil type used: solid/animal / mixed / liquid vegetable oil only.
            22542 => [
                106432 => 10,
                106433 => 20,
                106434 => 70,
            ],
        ];
    }
}
