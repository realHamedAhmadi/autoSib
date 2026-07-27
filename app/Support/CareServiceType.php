<?php

namespace App\Support;

use App\Services\Sib\Care\ExecuteCares\DiabeticService;
use App\Services\Sib\Care\ExecuteCares\HyperTensionService;
use App\Services\Sib\Care\ExecuteCares\Young\BMIService;
use App\Services\Sib\Care\ExecuteCares\Young\DentalHealthService;
use App\Services\Sib\Care\ExecuteCares\Young\DrugUseService;
use App\Services\Sib\Care\ExecuteCares\Young\HyperTensionRiskService;
use App\Services\Sib\Care\ExecuteCares\Young\MentalHealthService;
use App\Services\Sib\Care\ExecuteCares\Young\PhysicalActivityService;

enum CareServiceType: string
{
    case DIABETIC = 'diabetic';
    case HYPER_TENSION = 'hyper_tension';

    case YOUNG_PHYSICAL_ACTIVITY='young:physical_activity';
    case YOUNG_MENTAL_HEALTH='young:mental_health';
    case YOUNG_DRUG_USE='young:drug_use';
    case YOUNG_BMI='young:bmi';
    case YOUNG_HYPER_TENSION_RISK='young:hyper_tension_risk';
    case YOUNG_DENTAL_HEALTH='young:dental_health';


    /**
     * Get the fully qualified class name for the service.
     */
    public function getClassName(): string
    {
        return match ($this) {
            self::DIABETIC => DiabeticService::class,
            self::HYPER_TENSION => HyperTensionService::class,
            //young
            self::YOUNG_PHYSICAL_ACTIVITY=>PhysicalActivityService::class,
            self::YOUNG_MENTAL_HEALTH=>MentalHealthService::class,
            self::YOUNG_DRUG_USE=>DrugUseService::class,
            self::YOUNG_BMI=>BMIService::class,
            self::YOUNG_HYPER_TENSION_RISK=>HyperTensionRiskService::class,
            self::YOUNG_DENTAL_HEALTH=>DentalHealthService::class,
        };
    }
}

