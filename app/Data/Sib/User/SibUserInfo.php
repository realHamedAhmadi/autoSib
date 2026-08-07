<?php

namespace App\Data\Sib\User;

use Illuminate\Support\Carbon;

final readonly class SibUserInfo
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $family,
        public ?string $mobile,
        public ?string $nationalId,
        public ?int $gender,
        public ?int $maritalStatus,
        public ?Carbon $birthDate,
        public ?int $networkId,
        public ?int $areaId,
        public ?string $userToken,
        public ?array $sicks=[]
    ) {
    }

    /**
     * Create DTO from the API response payload.
     *
     * @param array<string, mixed> $data
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            userId: (int) ($data['Id_User'] ?? 0),
            name: (string) ($data['Name'] ?? ''),
            family: (string) ($data['Family'] ?? ''),
            mobile: isset($data['PhoneM']) ? (string) $data['PhoneM'] : null,
            nationalId: isset($data['NationalID']) ? (string) $data['NationalID'] : null,
            gender: isset($data['Gender']) ? (string) $data['Gender'] : null,
            maritalStatus: isset($data['Id_Married']) ? (string) $data['Id_Married'] : null,
            birthDate: isset($data['BirthDateM']) ? Carbon::parse($data['BirthDateM']) : null,
            networkId: isset($data['Id_Network']) ? (int) $data['Id_Network'] : null,
            areaId: isset($data['Id_Area']) ? (int) $data['Id_Area'] : null,
            userToken: $data['UserToken']['Data']??null,
            sicks: array_map(
                fn (array $item) => SibEventSick::fromArray($item),
                $data['EventSicks'] ?? []
            )
        );
    }

    /**
     * Get the full name of the user.
     */
    public function fullName(): string
    {
        return trim("{$this->name} {$this->family}");
    }
}
