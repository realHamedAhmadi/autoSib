<?php

namespace App\Services\Sib\Care;

use App\Data\Sib\Care\ChildCareIndexItem;
use App\Services\Sib\SibHttpClient;

final class SibChildCareIndexService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function listCompleted(string $accessToken): array
    {
        return $this->fetch($accessToken, 100);
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function listPending(string $accessToken): array
    {
        return $this->fetch($accessToken, 121);
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function fetch(string $accessToken, int $status): array
    {
        $response = $this->client
            ->request($accessToken)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/service/family-care-list',
            ])
            ->get("/api/sib/v1/Prm/FmlyChildIndex/{$status}");

        $data = $this->client->data($response);

        if (! is_array($data)) {
            return [];
        }

        return array_map(
            static fn (array $item): ChildCareIndexItem => ChildCareIndexItem::fromApiResponse($item),
            array_filter($data, 'is_array'),
        );
    }
}
