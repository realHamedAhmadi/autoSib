<?php

namespace App\Data\User;

use App\Support\MentalScreeningType;

class UserPayload
{
    public function __construct(
        public ?MentalScreeningType $mental,
        public ?string $hash,
        public ?string $userId,
    )
    {
    }

    public static function fromArray(array $data=[]):static
    {
        return new self(
          mental: isset($data['mental'])?MentalScreeningType::tryFrom($data['mental']):MentalScreeningType::NEGATIVE,
          hash: $data['hash']??null,
          userId: $data['userId']??null
        );
    }
}
