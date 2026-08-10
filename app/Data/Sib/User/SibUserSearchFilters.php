<?php

namespace App\Data\Sib\User;
final class SibUserSearchFilters
{

    public function __construct(
        public ?string $nationalId = null,
        public ?int    $fromAgeYears = null,
        public ?int    $toAgeYears = null,
        public ?array   $idSick = [],
        public ?array   $idFamilyRelation = [],
        public ?array   $idBlockNumber = [],
        public ?string $name = null,
        public ?string $family = null,
        public ?string $phone = null,
        public ?int    $gender = null,
        public ?array  $idMarriageType=[],
        public ?int    $countPerPage=null,
        public ?int    $currentPageNumber=null,
        public ?int    $conditionNetwork = null,
    )
    {
        $this->currentPageNumber=$this->currentPageNumber?:1;
        $this->countPerPage=$this->countPerPage?:50;
    }

    public function toQuery(): array
    {
        return array_filter([
            'NationalId' => $this->nationalId,
            'Name' => $this->name,
            'Family' => $this->family,
            'FromAge' => $this->ageToDays($this->fromAgeYears),
            'ToAge' =>$this->ageToDays( $this->toAgeYears),
            'Id_Sick' => $this->idSick?array_values($this->idSick):null,
            'ConditionNetwork' => $this->conditionNetwork,
            'Id_BlockNumber' => $this->idBlockNumber?array_values($this->idBlockNumber):null,
            'Id_FamilyRelation' => $this->idFamilyRelation?array_values($this->idFamilyRelation):null,
            //'PhoneM' => $this->phone,
            'Gender' => $this->gender,
            'Id_MarriageTyp' => $this->idMarriageType?array_values($this->idMarriageType):null,
            'CurrentPageNumber' => $this->currentPageNumber,
            'CountPerPage' => $this->countPerPage,
        ], static fn($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function ageToDays(?int $age): ?int
    {
        if ($age === null) {
            return null;
        }

        return (int) round($age * 365.25);
    }
}

