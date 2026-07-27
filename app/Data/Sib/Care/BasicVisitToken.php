<?php

namespace App\Data\Sib\Care;

final class BasicVisitToken
{
    public function __construct(
        public readonly string $token,
        public readonly string $expiration,
        public readonly string $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['Data'],
            expiration: $data['Expiration'],
            type: $data['Type'],
        );
    }
}
