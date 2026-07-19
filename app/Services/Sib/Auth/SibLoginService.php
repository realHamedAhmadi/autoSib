<?php

namespace App\Services\Sib\Auth;

use App\Data\Sib\Auth\AuthToken;
use App\Services\Sib\SibHttpClient;

final class SibLoginService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    public function login(string $username, string $password): AuthToken
    {
        $response = $this->client
            ->request()
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/login',
            ])
            ->post('/api/auth/v1/auth/Login', [
                'username' => $username,
                'password' => $password,
                'clientType' => config('sib.client_type'),
                'authMethod' => config('sib.auth_method'),
            ]);

        return AuthToken::fromApiResponse(
            $this->client->data($response)
        );
    }
}
