<?php

namespace App\Contracts\Cares;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;

interface CareHandlerInterface
{
    /**
     * Execute the specific care action.
     *
     * @param CompletedCareData $olderCareDate  SIB care user data
     * @param SibUserInfo $userInfo
     * @param ?array $payload
      @return array Result of execution to be persisted
     */
    public function handle(CompletedCareData $olderCareDate,SibUserInfo $userInfo,?array $payload): array;
}
