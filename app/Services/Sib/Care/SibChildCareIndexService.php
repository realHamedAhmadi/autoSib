<?php

namespace App\Services\Sib\Care;

use App\Data\Sib\Care\ChildCareIndexItem;
use App\Services\Sib\SibHttpClient;
use Illuminate\Support\Facades\Log;

final class SibChildCareIndexService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function listCompleted(?string $adminUserIdentifier = null): array
    {
        return $this->fetch(100,$adminUserIdentifier);
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function listPending(?string $adminUserIdentifier = null): array
    {
        return $this->fetch( 121,$adminUserIdentifier);
    }

    /**
     * @return array<int, ChildCareIndexItem>
     */
    public function fetch(int $status,?string $adminUserIdentifier = null): array
    {
        $response = $this->client
            ->request($adminUserIdentifier)
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

    public function getHashFrom(int $formId,?string $adminUserIdentifier = null):ChildCareIndexItem | null
    {
        $cares=$this->listCompleted($adminUserIdentifier);
        foreach ($cares as $care){
            if ($care->id==$formId){
                return $care;
            }
        }
        return null;
    }
}
