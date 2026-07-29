<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Support\MentalScreeningType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;
use App\Data\Sib\User\SibUserInfo;

class MentalHealthService implements CareHandlerInterface, CareAlreadyTakenCheckerInterface
{
    /**
     * Answer codes mapped by score (4: Always to 0: Never)
     */
    private const QUESTION_MAP = [
        16247 => [4 => 100056, 3 => 100057, 2 => 100058, 1 => 100059, 0 => 100060],
        16248 => [4 => 100062, 3 => 100063, 2 => 100064, 1 => 100065, 0 => 100066],
        16249 => [4 => 100068, 3 => 100069, 2 => 100070, 1 => 100071, 0 => 100072],
        16250 => [4 => 100074, 3 => 100075, 2 => 100076, 1 => 100077, 0 => 100078],
        16251 => [4 => 100080, 3 => 100081, 2 => 100082, 1 => 100083, 0 => 100084],
        16252 => [4 => 100086, 3 => 100087, 2 => 100088, 1 => 100089, 0 => 100090],
    ];

    private const ANXIETY_QUESTIONS = [16247, 16249];
    private const DEPRESSION_QUESTIONS = [16248, 16250, 16251, 16252];

    // Conditions that always return 0 (Not applicable/No)
    private const STATIC_ZERO_CONDITIONS = [16275, 28136, 23132, 16258, 16259, 23967];

    public function alreadyTaken(Carbon $latestVisitDate): bool
    {
        return Jalalian::fromCarbon($latestVisitDate)->getYear()==Jalalian::now()->getYear();
    }

    public function handle(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?array $payload): array
    {
        // Example: This could be passed via DTO or configuration
        return $this->generate(MentalScreeningType::NEGATIVE);
    }

    /**
     * Main generator logic
     */
    public function generate(MentalScreeningType $type): array
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
            MentalScreeningType::POSITIVE_ANXIETY => in_array($questionId, self::ANXIETY_QUESTIONS) ? [3, 4] : [0, 1, 2, 3, 4],
            MentalScreeningType::POSITIVE_DEPRESSION => in_array($questionId, self::DEPRESSION_QUESTIONS) ? [3, 4] : [0, 1, 2, 3, 4],
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

        $static = collect(self::STATIC_ZERO_CONDITIONS)->map(fn($id) => [
            "Id_Condition" => $id,
            "Answer" => 0,
            "PostProcessAnswer" => 0,
        ]);

        return array_merge($initial, $scoredAnswers->toArray(), $static->toArray());
    }
}
