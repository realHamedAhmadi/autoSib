<?php

namespace App\Services\Sib\User;

use App\Data\Sib\Auth\AuthToken;
use App\Data\Sib\User\SibAdminUserInfo;
use App\Data\Sib\User\SibUserInfo;
use App\Services\Sib\SibHttpClient;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

final class SibUserService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    function selectUser(string $nationalId, string $userToken,?string $adminUserIdentifier = null)
    {
        $response=$this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/checkTkn',
            ])
            ->post('/api/sib/v1/User/Select',[
                'select'=>true,
                'userToken'=>$userToken,
                'nationalId'=>$nationalId
            ]);

            $data = $this->client->data($response);
        $newAccessToken = $data['Token']['Data'] ?? null;

        if (! is_string($newAccessToken) || blank($newAccessToken)) {
            throw new UnexpectedValueException(
                'SIB SetRole response does not contain a valid JWT.'
            );
        }

        return AuthToken::fromApiResponse($data['Token']);
    }

    function getInfoByNationalId($nationalId,?string $adminUserIdentifier = null):SibUserInfo
    {
        $response=$this->client
            ->request($adminUserIdentifier)
            ->get("/api/sib/v1/User/$nationalId/Preview");

        $data = $this->client->data($response);

        return SibUserInfo::fromApiResponse($data);
    }

    function getInfoByToken(string $userToken,?string $adminUserIdentifier = null):SibUserInfo
    {
        $response=$this->client
            ->request($adminUserIdentifier)
            ->post('/api/sib/v1/User/GetByToken',[
                'userToken'=>$userToken,
            ]);

            $data = $this->client->data($response);

        return SibUserInfo::fromApiResponse($data);
    }

    function getFullInfo(string $userToken,?string $adminUserIdentifier = null):SibUserInfo
    {
        $response=$this->client
            ->request($adminUserIdentifier)
            ->post("/api/sib/v1/UserFile/EHR",[
                'userToken'=>$userToken
            ]);

        $data = $this->client->data($response);
        $userData=$data['UserInfo'];
        $userData['EventSicks']=$data['EventSicks'];
        return SibUserInfo::fromApiResponse($userData);
    }


}
