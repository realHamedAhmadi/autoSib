<?php

namespace App\Data\Sib\Care;

final class BasicCompletedCare
{
    public function __construct(
        public readonly string $date,
        public readonly string $roleUserName,
        public readonly ?string $childIndex,
        public readonly int $idChildIndex,
        public readonly int $idRoleUser,
        public readonly int $idBasicVisit,
        public readonly BasicVisitToken $basicVisitToken,
        public readonly string $titleAgeOnVisit,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            date: $data['Date_'],
            roleUserName: $data['RoleUserName'],
            childIndex: $data['ChildIndex'],
            idChildIndex: $data['Id_ChildIndex'],
            idRoleUser: $data['Id_RoleUser'],
            idBasicVisit: $data['Id_BasicVisit'],
            basicVisitToken: BasicVisitToken::fromArray($data['BasicVisitToken']),
            titleAgeOnVisit: $data['TitleAgeOnVisit'],
        );
    }
}
