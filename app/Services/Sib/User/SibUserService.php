<?php

namespace App\Services\Sib\User;

use App\Data\Sib\User\SibUserInfo;
use App\Services\Sib\SibHttpClient;

final class SibUserService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * Fetch the currently authenticated user's profile information.
     *
     * @param string $accessToken The JWT token stored in cookie/header.
     * @return SibUserInfo
     */
    public function getUserInfo(string $accessToken): SibUserInfo
    {
        $pendingRequest = $this->client
            ->request($accessToken)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/checkTkn',
            ]);

        \Illuminate\Support\Facades\Log::info('Outgoing Headers Dump', [
            'headers' => $pendingRequest->getOptions()['headers'] ?? [],
            'cookies' => $pendingRequest->getOptions()['cookies'] ?? 'No cookies nested',
        ]);
        $response=$pendingRequest->get('/api/sib/v1/AdminUser/UserInfo');

        $data = $this->client->data($response);

        return SibUserInfo::fromApiResponse(is_array($data) ? $data : []);
    }
}
