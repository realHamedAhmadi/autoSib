<?php

namespace App\Services\Sib\Care\ExecuteCares\Young;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;

class DentalHealthService extends BaseYoungCareService
{
    public function handle(
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
