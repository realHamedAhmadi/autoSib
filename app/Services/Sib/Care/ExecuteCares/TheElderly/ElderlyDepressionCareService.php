<?php

namespace App\Services\Sib\Care\ExecuteCares\TheElderly;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Support\MentalScreeningType;
use Illuminate\Support\Str;

class ElderlyDepressionCareService extends BaseElderlyCareService
{
    /**
     * Map of GDS-11 question conditions to their SIB value codes based on scored point (1 or 0)
     */
    protected const QUESTION_MAP = [
        // Q1: Satisfied with life? (Reverse: No = 1 point, Yes = 0 points)
        21184 => [1 => 105197, 0 => 105196],
        // Q2: Empty life? (Direct: Yes = 1 point, No = 0 points)
        21186 => [1 => 105198, 0 => 105199],
        // Q3: Often bored? (Direct: Yes = 1 point, No = 0 points)
        21188 => [1 => 105200, 0 => 105201],
        // Q4: Good spirits? (Reverse: No = 1 point, Yes = 0 points)
        21191 => [1 => 105203, 0 => 105202],
        // Q5: Fear of bad things? (Direct: Yes = 1 point, No = 0 points)
        21193 => [1 => 105204, 0 => 105205],
        // Q6: Happy most of the time? (Reverse: No = 1 point, Yes = 0 points)
        21195 => [1 => 105207, 0 => 105206],
        // Q7: Feel helpless? (Direct: Yes = 1 point, No = 0 points)
        21197 => [1 => 105208, 0 => 105209],
        // Q8: Wonderful to be alive? (Reverse: No = 1 point, Yes = 0 points)
        21201 => [1 => 105211, 0 => 105210],
        // Q9: Feel worthless? (Direct: Yes = 1 point, No = 0 points)
        21204 => [1 => 105212, 0 => 105213],
        // Q10: Situation hopeless? (Direct: Yes = 1 point, No = 0 points)
        21215 => [1 => 105214, 0 => 105215],
        // Q11: Others better off? (Direct: Yes = 1 point, No = 0 points)
        21217 => [1 => 105216, 0 => 105217],
    ];

    protected bool $isSick=false;

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $type = null;

        // Detect screening type based on pre-existing conditions/sicknesses
        foreach ($userInfo->sicks as $sick) {
            if (Str::contains($sick->sick, MentalScreeningType::POSITIVE_DEPRESSION->value)) {
                $type = MentalScreeningType::POSITIVE_DEPRESSION;
                $this->isSick=true;
                break;
            }
        }

        // Fallback to payload parameter or default to negative screening
        if (!$type) {
            $type = $payload->mental ?? MentalScreeningType::NEGATIVE;
        }

        return $this->generate($type);
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    /**
     * Generate answers based on screening type outcome probability
     */
    private function generate(MentalScreeningType $type): array
    {
        do {
            $responses = [];
            $totalScore = 0;

            foreach (self::QUESTION_MAP as $qId => $scores) {
                $score = $this->pickWeightedScore($type);
                $totalScore += $score;

                $responses[] = [
                    "Id_Condition" => $qId,
                    "Answer" => $scores[$score],
                    "PostProcessAnswer" => $scores[$score],
                ];
            }
        } while (!$this->isScoreValid($type, $totalScore));

        return $this->formatFinalPayload($responses, $type);
    }

    /**
     * Pick score (0 or 1) with weighted probability matching the expected result
     */
    private function pickWeightedScore(MentalScreeningType $type): int
    {
        $rand = rand(1, 100);

        if ($type === MentalScreeningType::NEGATIVE) {
            // 80% chance of normal (0 points), 20% chance of depressive symptom (1 point)
            return $rand <= 80 ? 0 : 1;
        }

        // 70% chance of depressive symptom (1 point), 30% chance of normal (0 points)
        return $rand <= 70 ? 1 : 0;
    }

    /**
     * Verify if total calculated score matches the GDS-11 screening threshold
     */
    private function isScoreValid(MentalScreeningType $type, int $totalScore): bool
    {
        if ($type === MentalScreeningType::NEGATIVE) {
            return $totalScore < 6;
        }

        return $totalScore >= 6;
    }

    /**
     * Format and combine all conditions into the final SIB API payload structure
     */
    private function formatFinalPayload(array $scoredAnswers, MentalScreeningType $type): array
    {
        // 29694: Screening conducted in Persian (Yes = 1)
        $payload = [
            [
                "Id_Condition" => 29694,
                "Answer" => 1,
                "PostProcessAnswer" => 1,
            ]
        ];

        // 35720: Refer to doctor condition
        $payload[] = [
            "Id_Condition" => 35720,
            "Answer" => 0,
            "PostProcessAnswer" => 0,
        ];

        // History of psychiatric disorders
        $payload[] = [
            "Id_Condition" => 23255,
            "Answer" => $a=$this->isSick?1:0,
            "PostProcessAnswer" => $a,
        ];

        if ($type==MentalScreeningType::NEGATIVE){
            // Wants to attend group training
            $payload[] = [
                "Id_Condition" => 28136,
                "Answer" => 0,
                "PostProcessAnswer" => 0,
            ];
        }
        // Merge generated scored answers
        return array_merge($payload, $scoredAnswers);
    }
}
