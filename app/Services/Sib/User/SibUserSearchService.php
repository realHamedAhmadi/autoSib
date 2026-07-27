<?php

namespace App\Services\Sib\User;

use App\Data\Sib\User\SibUserSearchFilters;
use App\Data\Sib\User\SibUserSummary;
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
    public function search(?string $adminUserIdentifier, SibUserSearchFilters $filters): SibUserSummary|array
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/register-census/service-recipient',
            ])
            ->get('/api/sib/v1/User/Search', $filters->toQuery());

        $data = $this->client->data($response);

        return array_map(
            static fn (array $item): SibUserSummary => SibUserSummary::fromApiResponse($item),
            array_filter($data, 'is_array'),
        );
    }
}
