<?php

namespace App\Data\Sib\Auth;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class AuthToken
{
    public function __construct(
        public string $jwt,
        public CarbonImmutable $expiresAt,
        public string $type,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        $jwt = $data['JWT'] ?? null;
        $expirationDate = $data['ExpirationDate'] ?? null;
        $type = $data['Type'] ?? null;

        if (
            ! is_string($jwt) ||
            $jwt === '' ||
            ! is_string($expirationDate) ||
            ! is_string($type)
        ) {
            throw new InvalidArgumentException(
                'The SIB login response does not contain a valid token.'
            );
        }

        return new self(
            jwt: $jwt,
            expiresAt: CarbonImmutable::parse($expirationDate),
            type: $type,
        );
    }

    public function isExpired(int $leewaySeconds = 60): bool
    {
        return $this->expiresAt->subSeconds($leewaySeconds)->isPast();
    }
}
