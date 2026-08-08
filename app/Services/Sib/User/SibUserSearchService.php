<?php

namespace App\Services\Sib\User;

use App\Data\Sib\SibServiceGroup;
use App\Data\Sib\User\SibUserSearchFilters;
use App\Data\Sib\User\SibUserSummary;
use App\Services\Sib\SibHttpClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class SibUserSearchService
{
    public function __construct(
        private readonly SibHttpClient $client,
    ) {
    }

    /**
     * Search users and return a Laravel-like paginated collection.
     */
    public function search(?string $adminUserIdentifier, SibUserSearchFilters $filters): LengthAwarePaginator
    {
        $totalCount=$this->count($adminUserIdentifier,$filters);

        $response = $this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/register-census/service-recipient',
            ])
            ->get('/api/sib/v1/User/Search', $filters->toQuery());

        $data = $this->client->data($response);
        // Ensure data is an array of items
        $items = array_filter($data, 'is_array');

        // Map array items to DTOs
        $mappedItems = array_map(
            static fn (array $item): SibUserSummary => SibUserSummary::fromApiResponse($item),
            $items,
        );

        $perPage = $filters->countPerPage;
        $currentPage = $filters->currentPageNumber;

        return new LengthAwarePaginator(
            collect($mappedItems),
            $totalCount,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function count(?string $adminUserIdentifier, SibUserSearchFilters $filters):int
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->get('/api/sib/v1/User/Search/Count', $filters->toQuery());

        return $this->client->data($response);
    }

    public function serviceGroup(?string $adminUserIdentifier,int $networkId)
    {
        $response = $this->client
            ->request($adminUserIdentifier)
            ->withHeaders([
                'Referer' => config('sib.base_url') . '/sibnew/register-census/service-recipient',
            ])
            ->get('/api/sib/v1/BlockNumber/Search', [
                'Id_Network'=>$networkId
            ]);
        $data=$this->client->data($response);
        return  array_map(
            static fn (array $item): SibServiceGroup => SibServiceGroup::fromApiResponse($item),
            array_filter($data,'is_array'),
        );
    }
}
