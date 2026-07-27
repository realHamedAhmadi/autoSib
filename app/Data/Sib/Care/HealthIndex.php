<?php

namespace App\Data\Sib\Care;

class HealthIndex
{
    public function __construct(
        public readonly float|int|null $value1,
        public readonly int $id,
        public readonly string $title,
        public readonly int $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            value1: isset($data['Value1']) ? (is_float($data['Value1']) || str_contains((string) $data['Value1'], '.') ? (float) $data['Value1'] : (int) $data['Value1']) : null,
            id: (int) $data['Id'],
            title: (string) $data['Title'],
            type: (int) $data['Typ'],
        );
    }
}
