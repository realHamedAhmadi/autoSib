<?php

namespace App\Services\Sib\Care;

use App\Data\Sib\Care\BasicCompletedCare;
use App\Data\Sib\Care\CompletedCareData;
use App\Services\Sib\SibHttpClient;
use Illuminate\Support\Facades\Log;

final class SibCareService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    public function listOfCompleted(string $userToken,?string $adminUserIdentifier = null): array
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->post("/api/sib/v1/UserFile/EHR",[
                'userToken'=>$userToken
            ]);
        $data = $this->client->data($response);

        if (! is_array($data)) {
            return [];
        }
        return array_map(
            static fn (array $item): BasicCompletedCare => BasicCompletedCare::fromApiResponse($item),
            array_filter($data['BasicVisits'], 'is_array'),
        );
    }

    public function CompletedCareData(string $visitToken,?string $adminUserIdentifier = null):CompletedCareData
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->post("/api/sib/v1/FmlyForm/Preview",[
                'basicVisitToken'=>$visitToken
            ]);

        $data = $this->client->data($response);

        return CompletedCareData::fromApiResponse($data['HealthCarePreview']);
    }

    function saveFrom($formId,$formHash,$hash=null,$answers=null,?string $adminUserIdentifier = null)
    {
        $d['ChildIndexHash']=$formHash;
        $d['id_ChildIndex']=$formId;
        $d['Hash']=$hash;
        if (!$hash){
            unset($d['Hash']);
        }
        $d['answers']=$answers;
        $d['id_Priority']=11;
        if (!$answers){
            $d['Comment']=null;
            $d['id_Priority']=-1;
        }
        $response = $this->client
            ->request($adminUserIdentifier)
            ->post("/api/sib/v1/FmlyForm/Save",$d);

        $data = $this->client->data($response);
        return $data['Hash'];
    }
}
