<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;
use App\Services\Sib\SibHttpClient;

class DentalHealthService extends BaseYoungCareService
{
    public function firstForm(
        CompletedCareData $olderCareDate,
        SibUserInfo $userInfo,
        ?UserPayload $payload,
    ): array {
        $answers = [];

        foreach ($this->staticAnswers() as $questionId => $value) {
            $answers[] = [
                'Id_Condition' => $questionId,
                'Answer' => $value,
                'PostProcessAnswer' => $value,
            ];
        }

        return $answers;
    }

    public function secondForm(CompletedCareData $olderCareDate, SibUserInfo $userInfo, ?UserPayload $payload): array
    {
        return [];
    }

    public function action(int $sibAdminUserId, SibUserInfo $userInfo, ?UserPayload $payload): void
    {
        $arr=[
            'actions'=>[
                [
                    "id_ToothActivity"=>100,
                    "id_ToothNumber"=> 1000
                ],
                [
                    "id_ToothActivity"=> 127,
                    "id_ToothNumber"=>1000
                ]
            ],
            'needs'=>[],
            'states'=>[]
        ];
        $client=app()->make(SibHttpClient::class);
        $client->request($sibAdminUserId)
            ->post('/api/sib/v1/Tooth/SaveForm', $arr);

    }

    /**
     * @return array<int, int>
     */
    private function staticAnswers(): array
    {
        return [
            12578 => 1,
            12574 => 0,
            12575 => 0,
            12580 => 0,
            12581 => 0,
            12576 => 0,
            15833 => 0,
            12582 => 0,
            12583 => 0,
            15832 => 0,
        ];
    }
}
