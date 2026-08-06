<?php

namespace App\Contracts\Cares;

use App\Data\Sib\Care\CompletedCareData;
use App\Data\Sib\User\SibUserInfo;
use App\Data\User\UserPayload;

interface CareHandlerInterface
{
    /**
     * @param SibUserInfo $userInfo
     * @return bool
     */
    public function hasCare(SibUserInfo $userInfo):bool;

    /**
     * Execute the specific care action.
     *
     * @param CompletedCareData $olderCareDate  SIB care user data
     * @param SibUserInfo $userInfo
     * @param ?array $payload
      @return array Result of execution to be persisted
     */
    public function firstForm(CompletedCareData $olderCareDate,SibUserInfo $userInfo,?UserPayload $payload): array;
    /**
     * Execute the specific care action.
     *
     * @param CompletedCareData $olderCareDate  SIB care user data
     * @param SibUserInfo $userInfo
     * @param ?array $payload
      @return array Result of execution to be persisted
     */
    public function secondForm(CompletedCareData $olderCareDate,SibUserInfo $userInfo,?UserPayload $payload): array;

}
