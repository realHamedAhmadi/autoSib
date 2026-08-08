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

        // Ensure data is an array of items
        $items = array_filter($data, 'is_array');

        // Manually filter by phone if provided
        if ($filters->phone !== null && $filters->phone !== '') {
            $searchPhone = normalizeNumber($filters->phone);

            $items = array_filter(
                $items,
                static function (array $item) use ($searchPhone): bool {
                    $itemPhone = $item['PhoneM'] ?? null;

                    if ($itemPhone === null) {
                        return false;
                    }

                    $normalizedItemPhone = normalizeNumber((string) $itemPhone);

                    // Check for exact match or suffix match (to handle 0 vs 98 prefixes)
                    return $normalizedItemPhone === $searchPhone
                        || str_ends_with($normalizedItemPhone, ltrim($searchPhone, '0'))
                        || str_ends_with($searchPhone, ltrim($normalizedItemPhone, '0'));
                }
            );
        }

        return array_map(
            static fn (array $item): SibUserSummary => SibUserSummary::fromApiResponse($item),
            $items,
        );
    }

}
