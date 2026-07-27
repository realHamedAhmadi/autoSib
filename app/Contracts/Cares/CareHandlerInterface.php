<?php

namespace App\Contracts\Cares;

use App\Data\Sib\Care\CompletedCareData;

interface CareHandlerInterface
{
    /**
     * Execute the specific care action.
     *
     * @param CompletedCareData $olderCareDate  SIB care user data
      @return array Result of execution to be persisted
     */
    public function handle(CompletedCareData $olderCareDate): array;
}
