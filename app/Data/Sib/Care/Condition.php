<?php

namespace App\Data\Sib\Care;

class Condition
{
    public function __construct(
        public readonly ?string $answer,
        public readonly int $id,
        public readonly int $idAnswer,
        public readonly string $title,
        public readonly int $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            answer: $data['Answer'] ?? null,
            id: (int) $data['Id'],
            idAnswer: (int) $data['Id_Answer'],
            title: (string) $data['Title'],
            type: (int) $data['Typ'],
        );
    }
}
