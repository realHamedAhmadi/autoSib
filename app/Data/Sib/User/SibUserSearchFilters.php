<?php

namespace App\Data\Sib\User;

final readonly class SibUserSearchFilters
{
    public function __construct(
        public int $currentPageNumber = 1,
        public ?int $countPerPage = null,
        public ?string $nationalId = null,
        public ?string $name = null,
        public ?string $family = null,
        public ?string $phoneMobile = null,
        public ?int $conditionNetwork = null,
        public ?int $idBlockNumber = null,
        public ?int $gender = null,
    ) {
    }

    /**
     * @return array<string, scalar>
     */
    public function toQuery(): array
    {
        return array_filter([
            'CurrentPageNumber' => $this->currentPageNumber,
            'CountPerPage' => $this->countPerPage,
            'NationalID' => $this->nationalId,
            'Name' => $this->name,
            'Family' => $this->family,
            'PhoneM' => $this->phoneMobile,
            'ConditionNetwork' => $this->conditionNetwork,
            'Id_BlockNumber' => $this->idBlockNumber,
            'Gender' => $this->gender,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
