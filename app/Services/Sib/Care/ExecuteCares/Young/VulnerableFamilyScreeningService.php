<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Support\GenderStatus;
use App\Support\MaritalStatus;

class VulnerableFamilyScreeningService extends BaseYoungCareService
{
    public function firstForm(
        CompletedCareData $olderCareDate,
        SibUserInfo $userInfo,
        ?UserPayload $payload,
    ): array {
        $answers = [];

        if ($userInfo->gender==GenderStatus::WOMAN->value && $userInfo->maritalStatus==MaritalStatus::MARRIED->value){
            // 1. Process Abuse/Violence questions (weighted 70/30)
            foreach ($this->abuseQuestions() as $questionId => $weights) {
                $answer = $this->pickWeightedAnswer($weights);
                $answers[] = [
                    'Id_Condition' => $questionId,
                    'Answer' => $answer,
                    'PostProcessAnswer' => $answer,
                ];
            }

            // 2. Process Divorce/Separation question (binary: 0 or 1)
            $answers[] = [
                'Id_Condition' => 19605,
                'Answer' => 0, // Default to 'No'
                'PostProcessAnswer' => 0,
            ];
        }

        // 3. Process Vulnerable Family checkboxes (default: 115611 - None)
        $answers[] = [
            'Id_Condition' => 28476,
            'Answer' => [115611],
            'PostProcessAnswer' => [115611],
        ];

        //job
        $answers[] = [
            'Id_Condition' => 29597,
            'Answer' => 1, // Default to 'yes'
            'PostProcessAnswer' => 1,
        ];

        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        //group learning
        $answers[] = [
            'Id_Condition' => 28136,
            'Answer' => 0, // Default to 'No'
            'PostProcessAnswer' => 0,
        ];

        return $answers;
    }

    /**
     * Abuse questions with 70% 'Never' and 30% 'Rarely' distribution.
     *
     * @return array<int, array<int, int>>
     */
    private function abuseQuestions(): array
    {
        return [
            16282 => [100094 => 70, 100095 => 30], // Hitting
            16283 => [100100 => 70, 100101 => 30], // Insult
            16284 => [100106 => 70, 100107 => 30], // Threat
            16285 => [100112 => 70, 100113 => 30], // Yelling
        ];
    }

    /**
     * @param array<int, int> $weights [answerId => probability weight]
     */
    private function pickWeightedAnswer(array $weights): int
    {
        $random = random_int(1, array_sum($weights));
        $currentWeight = 0;

        foreach ($weights as $answerId => $weight) {
            $currentWeight += $weight;

            if ($random <= $currentWeight) {
                return $answerId;
            }
        }

        throw new \LogicException('Unable to select a weighted answer.');
    }
}
