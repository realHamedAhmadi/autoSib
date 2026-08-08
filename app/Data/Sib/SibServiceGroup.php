<?php

namespace App\Data\Sib;

final readonly class SibServiceGroup
{
    public function __construct(
        public int $id,
        public int $idNetwork,
        public string $title,
        public string $network,
    ) {
    }

    /**
     * Map a single API array item to a DTO instance.
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: (int) ($data['Id'] ?? 0),
            idNetwork: (int) ($data['Id_Network'] ?? 0),
            title: (string) ($data['Title'] ?? ''),
            network: (string) ($data['Network'] ?? ''),
        );
    }
}
