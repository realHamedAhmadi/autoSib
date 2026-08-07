<?php

namespace App\Support;

use App\Services\Sib\Care\ExecuteCares\DiabeticService;
use App\Services\Sib\Care\ExecuteCares\HyperTensionService;
use App\Services\Sib\Care\ExecuteCares\MiddleAged\MiddleBMIService;
use App\Services\Sib\Care\ExecuteCares\MiddleAged\MiddleDrugUseService;
use App\Services\Sib\Care\ExecuteCares\MiddleAged\MiddleMentalHealthService;
use App\Services\Sib\Care\ExecuteCares\MiddleAged\MiddlePhysicalActivityService;
use App\Services\Sib\Care\ExecuteCares\MiddleAged\MiddleVulnerableFamilyScreeningService;
use App\Services\Sib\Care\ExecuteCares\SuspectedAsthmaIdentifierService;
use App\Services\Sib\Care\ExecuteCares\TheElderly\ElderlyBMIService;
use App\Services\Sib\Care\ExecuteCares\TheElderly\ElderlyDepressionCareService;
use App\Services\Sib\Care\ExecuteCares\TheElderly\ElderlyImbalanceCareService;
use App\Services\Sib\Care\ExecuteCares\TheElderly\ElderlyPhysicalActivityService;
use App\Services\Sib\Care\ExecuteCares\Young\BMIService;
use App\Services\Sib\Care\ExecuteCares\Young\DentalHealthService;
use App\Services\Sib\Care\ExecuteCares\Young\DrugUseService;
use App\Services\Sib\Care\ExecuteCares\Young\HyperTensionRiskService;
use App\Services\Sib\Care\ExecuteCares\Young\MentalHealthService;
use App\Services\Sib\Care\ExecuteCares\Young\PhysicalActivityService;
use App\Services\Sib\Care\ExecuteCares\Young\VulnerableFamilyScreeningService;

enum CareServiceType: string
{
    case DIABETIC = 'diabetic';
    case HYPER_TENSION = 'hyper_tension';

    case YOUNG_PHYSICAL_ACTIVITY='young:physical_activity';
    case YOUNG_MENTAL_HEALTH='young:mental_health';
    case YOUNG_VULNERABLE_FAMILY='young:vulnerable_family';
    case YOUNG_DRUG_USE='young:drug_use';
    case YOUNG_BMI='young:bmi';
    case YOUNG_HYPER_TENSION_RISK='young:hyper_tension_risk';
    case YOUNG_DENTAL_HEALTH='young:dental_health';
    case YOUNG_ASTHMA='young:asthma';

    case MIDDLE_PHYSICAL_ACTIVITY='middle:physical_activity';
    case MIDDLE_MENTAL_HEALTH='middle:mental_health';
    case MIDDLE_VULNERABLE_FAMILY='middle:vulnerable_family';
    case MIDDLE_DRUG_USE='middle:drug_use';
    case MIDDLE_BMI='middle:bmi';
    case MIDDLE_ASTHMA='middle:asthma';

    //the elderly
    case ELDERLY_BMI='elderly:bmi';
    case ELDERLY_PHYSICAL_ACTIVITY='elderly:physical_activity';
    case ELDERLY_DEPRESSION='elderly:depression';
    case ELDERLY_IMBALANCE='elderly:imbalance';
    case ELDERLY_ASTHMA='elderly:asthma';

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
            self::YOUNG_VULNERABLE_FAMILY=>VulnerableFamilyScreeningService::class,
            self::YOUNG_DRUG_USE=>DrugUseService::class,
            self::YOUNG_BMI=>BMIService::class,
            self::YOUNG_HYPER_TENSION_RISK=>HyperTensionRiskService::class,
            self::YOUNG_DENTAL_HEALTH=>DentalHealthService::class,
            self::YOUNG_ASTHMA=>SuspectedAsthmaIdentifierService::class,

            //middle aged
            self::MIDDLE_PHYSICAL_ACTIVITY=>MiddlePhysicalActivityService::class,
            self::MIDDLE_MENTAL_HEALTH=>MiddleMentalHealthService::class,
            self::MIDDLE_VULNERABLE_FAMILY=>MiddleVulnerableFamilyScreeningService::class,
            self::MIDDLE_DRUG_USE=>MiddleDrugUseService::class,
            self::MIDDLE_BMI=>MiddleBMIService::class,
            self::MIDDLE_ASTHMA=>SuspectedAsthmaIdentifierService::class,

            //the elderly
            self::ELDERLY_BMI=>ElderlyBMIService::class,
            self::ELDERLY_DEPRESSION=>ElderlyDepressionCareService::class,
            self::ELDERLY_IMBALANCE=>ElderlyImbalanceCareService::class,
            self::ELDERLY_PHYSICAL_ACTIVITY=>ElderlyPhysicalActivityService::class,
            self::ELDERLY_ASTHMA=>SuspectedAsthmaIdentifierService::class,
        };
    }
}

