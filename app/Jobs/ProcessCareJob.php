<?php

namespace App\Jobs;

use App\Services\Sib\Care\SibCareService;
use App\Services\Sib\Care\SibChildCareIndexService;
use App\Services\Sib\User\SibUserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCareJob implements ShouldQueue
{
    use Queueable;

    protected readonly SibUserService $sibUserService;
    protected readonly SibCareService $sibCareService;
    protected readonly SibChildCareIndexService $childCareIndexService;


    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly array $sibUsers,
    )
    {
        $this->sibUserService=app(SibUserService::class);
        $this->sibCareService=app(SibCareService::class);
        $this->childCareIndexService=app(SibChildCareIndexService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->sibUsers as $user){
            $token=$this->sibUserService->selectUser($user['id'],$user['token']);
            setCurrentUserToken($token);
            $oldData=$this->oldData($user['token']);
            $formHash=app(SibChildCareIndexService::class)->getHashFrom(8326);
            $hash=$this->sibCareService->saveFrom($formHash,null,true);
            $hash=$this->sibCareService->saveFrom($formHash,$hash);
            $this->sibCareService->saveFrom($formHash,$hash,true);
        }
    }

    protected function oldData(string $userToken)
    {
        $info=$this->sibUserService->getInfoByToken($userToken);
        $visits=$this->sibCareService->listOfCompleted($info->userToken);
        foreach ($visits as $visit){
            if ($visit->idChildIndex==8326){
                return $this->sibCareService->CompletedCareData($visit->basicVisitToken->token);
            }
        }
        return [];
    }

    function setForm()
    {

    }
}
