<?php

namespace App\Data\Sib\User;

final readonly class SibAdminUserInfo
{
    public function __construct(
        public int $userId,
        public ?int $roleUserId,
        public ?int $roleId,
        public string $name,
        public string $family,
        public ?string $mobile,
        public ?string $nationalId,
        public ?string $medicalNo,
        public ?int $networkId,
        public ?int $networkPosition,
        public ?int $networkStructureTypeId,
        public ?int $areaId,
        public ?int $state,
        public ?int $masterRoleId,
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
            roleUserId: isset($data['Id_RoleUser']) ? (int) $data['Id_RoleUser'] : null,
            roleId: isset($data['Id_Role']) ? (int) $data['Id_Role'] : null,
            name: (string) ($data['Name'] ?? ''),
            family: (string) ($data['Family'] ?? ''),
            mobile: isset($data['PhoneM']) ? (string) $data['PhoneM'] : null,
            nationalId: isset($data['NationalID']) ? (string) $data['NationalID'] : null,
            medicalNo: isset($data['MedicalNO']) ? (string) $data['MedicalNO'] : null,
            networkId: isset($data['Id_Network']) ? (int) $data['Id_Network'] : null,
            networkPosition: isset($data['NetworkPosition']) ? (int) $data['NetworkPosition'] : null,
            networkStructureTypeId: isset($data['Id_NetworkStructureType']) ? (int) $data['Id_NetworkStructureType'] : null,
            areaId: isset($data['Id_Area']) ? (int) $data['Id_Area'] : null,
            state: isset($data['State']) ? (int) $data['State'] : null,
            masterRoleId: isset($data['Id_MasterRole']) ? (int) $data['Id_MasterRole'] : null,
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
