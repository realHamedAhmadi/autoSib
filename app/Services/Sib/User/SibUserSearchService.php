<?php

namespace App\Services\Sib\User;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Services\Sib\SibHttpClient;

final class SibUserSearchService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $accessToken, SibUserSearchFilters $filters): array
    {
        $response = $this->client
            ->request($accessToken)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/register-census/service-recipient',
            ])
            ->get('/api/sib/v1/User/Search', $filters->toQuery());

        $data = $this->client->data($response);

        return is_array($data) ? $data : [];
    }
}
