<?php

namespace App\Data\Sib\User;
use Illuminate\Support\Carbon;

readonly class SibEventSick
{
    public function __construct(
        public int $userId,
        public Carbon $date,
        public ?Carbon $dateFinish,
        public Carbon $dateSeek,
        public int $id,
        public int $idSeek,
        public string $name,
        public string $nationalId,
        public string $ownerName,
        public string $seek
    ) {}

    /**
     * Create DTO from array data.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['Id_User'],
            date: Carbon::parse($data['Date_']),
            dateFinish: !empty($data['Date_Finish']) ? Carbon::parse($data['Date_Finish']) : null,
            dateSeek: Carbon::parse($data['Date_Seek']),
            id: (int) $data['Id'],
            idSeek: (int) $data['Id_Seek'],
            name: $data['Name'],
            nationalId: $data['NationalID'],
            ownerName: $data['OwnerName'],
            seek: $data['Seek']
        );
    }
}

