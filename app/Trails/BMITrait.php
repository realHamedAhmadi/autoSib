<?php

namespace App\Trails;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isNull;

trait BMITrait
{
    protected array $otherAnswers=[];
    protected array $threeOptionAnswers=[
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
    ];

    public function firstForm(
        CompletedCareData $olderCareDate,
        SibUserInfo $userInfo,
        ?UserPayload $payload,
    ): array {
        $this->addItems();
        $answers = [];
        $ids=[10001,10002,10004];
        foreach ($ids as $id){
            $answers[]=[
                "Id_Condition"=>$id,
                "Answer"=>$a=$olderCareDate->getAnswer($id),
                "PostProcessAnswer"=> $a
            ];
        }

        foreach ($this->threeOptionAnswers as $questionId => $options) {
            $answer = $this->pickWeightedAnswer($options);

            $answers[] = [
                'Id_Condition' => $questionId,
                'Answer' => $answer,
                'PostProcessAnswer' => $answer,
            ];
        }

        foreach ($this->otherAnswers as $key=>$an){
            $answers[]=[
                "Id_Condition"=>$key,
                "Answer"=>$a=(is_null($an)?$olderCareDate->getAnswer($key):$an),
                "PostProcessAnswer"=> $a
            ];
        }
        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    protected function addItems():void
    {

    }

    /**
     * @param array<int, int> $weights [answerId => probability weight]
     */
    protected function pickWeightedAnswer(array $weights): int
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
