<?php

namespace App\Data\Sib\User;

final readonly class SibUserSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $family,
        public ?string $mobile,
        public ?string $nationalId,
        public ?string $gender,
        public ?string $birthDate,
        public ?string $ageTitle,
        public ?int $blockNumberId,
        public ?string $blockNumberTitle,
        public ?string $userToken,
    ) {
    }

    public static function fromApiResponse(array $item): self
    {
        return new self(
            id: (int) ($item['Id_User'] ?? ''),
            name: (string) ($item['Name'] ?? ''),
            family: (string) ($item['Family'] ?? ''),
            mobile: isset($item['PhoneM']) ? (string) $item['PhoneM'] : null,
            nationalId: isset($item['NationalID']) ? (string) $item['NationalID'] : null,
            gender: isset($item['Gender']) ? (string) $item['Gender'] : null,
            birthDate: isset($item['BirthDate']) ? (string) $item['BirthDate'] : null,
            ageTitle: isset($item['Age_Title']) ? (string) $item['Age_Title'] : null,
            blockNumberId: isset($item['Id_BlockNumber']) ? (int) $item['Id_BlockNumber'] : null,
            blockNumberTitle: isset($item['Title_BlockNumber']) ? (string) $item['Title_BlockNumber'] : null,
            userToken: isset($item['UserToken']['Data']) ? (string) $item['UserToken']['Data'] : null,
        );
    }

    public function fullname():string
    {
        return  $this->name.' '.$this->family;
    }
}
