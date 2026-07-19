<?php

namespace App\Data\Sib\Auth;

final readonly class SibRoleItem
{
    public function __construct(
        public int $roleUserId,
        public string $title,
    ) {
    }

    public static function fromApiResponse(array $item): self
    {
        return new self(
            roleUserId: (int) ($item['Id_RoleUser'] ?? 0),
            title: (string) ($item['Title'] ?? ''),
        );
    }
}
