<?php

namespace App\Services\Sib\User;

use App\Data\Sib\Auth\AuthToken;
use App\Data\Sib\User\SibAdminUserInfo;
use App\Services\Sib\SibHttpClient;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

final class SibAdminUserService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * Fetch the currently authenticated user's profile information.
     *
     * @return SibAdminUserInfo
     */
    public function getUserInfo(): SibAdminUserInfo
    {
        $pendingRequest = $this->client
            ->request()
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/checkTkn',
            ]);

        $response=$pendingRequest->get('/api/sib/v1/AdminUser/UserInfo');

        $data = $this->client->data($response);

        return SibAdminUserInfo::fromApiResponse(is_array($data) ? $data : []);
    }
}
