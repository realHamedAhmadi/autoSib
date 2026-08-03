<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Contracts\Cares\CareAlreadyTakenCheckerInterface;
use App\Contracts\Cares\CareHandlerInterface;
use App\Data\Sib\Care\CompletedCareData;
use App\Data\User\UserPayload;
use Illuminate\Support\Carbon;
use App\Data\Sib\User\SibUserInfo;

class BMIService extends BaseYoungCareService
{
    public function firstForm(
        CompletedCareData $olderCareDate,
        SibUserInfo $userInfo,
        ?UserPayload $payload,
    ): array {
        $answers = [];
        $ids=[10001,10002,10004];
        foreach ($ids as $id){
            $answers[]=[
                "Id_Condition"=>$id,
                "Answer"=>$a=$olderCareDate->getAnswer($id),
                "PostProcessAnswer"=> $a
            ];
        }

        $answers[]=[
            "Id_Condition"=>25668,
            "Answer"=>0,
            "PostProcessAnswer"=> 0
        ];

        foreach ($this->threeOptionAnswers() as $questionId => $options) {
            $answer = $this->pickWeightedAnswer($options);

            $answers[] = [
                'Id_Condition' => $questionId,
                'Answer' => $answer,
                'PostProcessAnswer' => $answer,
            ];
        }

        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function threeOptionAnswers(): array
    {
        return [
            // Fruit consumption: rarely, less than 2 servings, 2 to 4+ servings.
            16029 => [
                100035 => 10,
                100036 => 20,
                100037 => 70,
            ],

            // Vegetable consumption: rarely, less than 3 servings, 3 to 5+ servings.
            16031 => [
                100038 => 10,
                100039 => 20,
                100040 => 70,
            ],

            // Dairy consumption: rarely, less than 2 servings, 2+ servings.
            16026 => [
                100026 => 10,
                100027 => 20,
                100028 => 70,
            ],

            // Salt shaker use: always, sometimes, rarely/never.
            16027 => [
                100029 => 10,
                100030 => 20,
                100031 => 70,
            ],

            // Fast-food consumption: 2+ times weekly, weekly, rarely/never.
            16028 => [
                100032 => 10,
                100033 => 20,
                100034 => 70,
            ],

            // Oil type: solid/animal, mixed, liquid vegetable oil only.
            16032 => [
                100041 => 10,
                100042 => 20,
                100043 => 70,
            ],

            // Physical activity: none, below 150 minutes, 150+ minutes weekly.
            16656 => [
                100428 => 10,
                100429 => 20,
                100430 => 70,
            ],
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
