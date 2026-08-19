<?php

namespace App\Trails;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Support\MentalScreeningType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait MentalCareTrail
{
    /**
     * Answer codes mapped by score (4: Always to 0: Never)
     */
    protected const QUESTION_MAP = [
        16247 => [4 => 100056, 3 => 100057, 2 => 100058, 1 => 100059, 0 => 100060],
        16248 => [4 => 100062, 3 => 100063, 2 => 100064, 1 => 100065, 0 => 100066],
        16249 => [4 => 100068, 3 => 100069, 2 => 100070, 1 => 100071, 0 => 100072],
        16250 => [4 => 100074, 3 => 100075, 2 => 100076, 1 => 100077, 0 => 100078],
        16251 => [4 => 100080, 3 => 100081, 2 => 100082, 1 => 100083, 0 => 100084],
        16252 => [4 => 100086, 3 => 100087, 2 => 100088, 1 => 100089, 0 => 100090],
    ];

    protected const ANXIETY_QUESTIONS = [16247, 16249];
    protected const DEPRESSION_QUESTIONS = [16248, 16250, 16251, 16252];

    public function firstForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        $type=null;
        foreach ($userInfo->sicks as $sick){
            if (Str::contains($sick->sickTitle,MentalScreeningType::POSITIVE_DEPRESSION->value,true)){
                $type=MentalScreeningType::POSITIVE_DEPRESSION;
                break;
            }
            if (Str::contains($sick->sickTitle,MentalScreeningType::POSITIVE_ANXIETY->value,true)){
                $type=MentalScreeningType::POSITIVE_ANXIETY;
                break;
            }
        }
        if (!$type){
            $type=$payload->mental;
        }
        // Example: This could be passed via DTO or configuration
        return $this->generate($type);
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    /**
     * Main generator logic
     */
    private function generate(MentalScreeningType $type): array
    {
        do {
            $responses = collect();
            $totalScore = 0;

            foreach (self::QUESTION_MAP as $qId => $scores) {
                $score = $this->pickScore($type, $qId);
                $totalScore += $score;

                $responses->push([
                    "Id_Condition" => $qId,
                    "Answer" => $scores[$score],
                    "PostProcessAnswer" => $scores[$score],
                ]);
            }
        } while (!$this->isScoreValid($type, $totalScore));

        return $this->formatFinalPayload($responses);
    }

    /**
     * Logic for picking a score based on screening type and question category
     */
    private function pickScore(MentalScreeningType $type, int $questionId): int
    {
        $ranges = match ($type) {
            MentalScreeningType::NEGATIVE => [0,0,0,1,1, 2],
            MentalScreeningType::POSITIVE_ANXIETY => in_array($questionId, self::ANXIETY_QUESTIONS) ? [2,3,3,3,4] : [0,1,1,2,2,2,3,4],
            MentalScreeningType::POSITIVE_DEPRESSION => in_array($questionId, self::DEPRESSION_QUESTIONS) ? [2,3,3,3,4] : [0,1,1,2,2,2,3,4],
        };

        return $ranges[array_rand($ranges)];
    }

    /**
     * Validate if the generated sum matches the required outcome
     */
    private function isScoreValid(MentalScreeningType $type, int $totalScore): bool
    {
        return $type === MentalScreeningType::NEGATIVE ? $totalScore < 10 : $totalScore >= 10;
    }

    /**
     * Merge initial condition, scored questions, and static zero conditions
     */
    private function formatFinalPayload(Collection $scoredAnswers): array
    {
        $initial = [
            ["Id_Condition" => 30087, "Answer" => 1, "PostProcessAnswer" => 1]
        ];

        $static = collect($this->zeroConditions)->map(fn($id) => [
            "Id_Condition" => $id,
            "Answer" => 0,
            "PostProcessAnswer" => 0,
        ]);

        return array_merge($initial, $scoredAnswers->toArray(), $static->toArray());
    }
}
