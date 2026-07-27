<?php

namespace App\Services\Sib\Auth;

use App\Data\Sib\Auth\AuthToken;
use App\Data\Sib\Auth\SibRoleItem;
use App\Services\Sib\SibHttpClient;
use UnexpectedValueException;

final class SibRoleService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * @return array<int, SibRoleItem>
     */
    public function getRoles(?string $adminUserIdentifier=null): array
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => $this->loginReferer(),
            ])
            ->get('/api/auth/v1/Auth/GetRoles');

        $data = $this->client->data($response);

        return array_values(array_map(
            static fn (array $item): SibRoleItem => SibRoleItem::fromApiResponse($item),
            array_filter($data, 'is_array'),
        ));
    }

    public function setRole(string $adminUserIdentifier, int $roleUserId): AuthToken
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => $this->loginReferer(),
            ])
            ->post('/api/auth/v1/Auth/SetRole', [
                'id_RoleUser' => $roleUserId,
            ]);

        $data = $this->client->data($response);

        $newAccessToken = $data['JWT'] ?? null;

        if (! is_string($newAccessToken) || blank($newAccessToken)) {
            throw new UnexpectedValueException(
                'SIB SetRole response does not contain a valid JWT.'
            );
        }

        return AuthToken::fromApiResponse($data);
    }

    private function loginReferer(): string
    {
        return rtrim((string) config('sib.base_url'), '/')
            . '/sibnew/login';
    }
}
