<?php

namespace App\Data\Sib\User;
use Illuminate\Support\Carbon;

readonly class SibEventSick
{
    public function __construct(
        public int $userId,
        public Carbon $date,
        public ?Carbon $finishDate,
        public Carbon $sickDate,
        public int $id,
        public int $sickId,
        public string $name,
        public string $nationalId,
        public string $ownerName,
        public string $sickTitle
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
            finishDate: !empty($data['Date_Finish']) ? Carbon::parse($data['Date_Finish']) : null,
            sickDate: Carbon::parse($data['Date_Seek']),
            id: (int) $data['Id'],
            sickId: (int) $data['Id_Seek'],
            name: $data['Name'],
            nationalId: $data['NationalID'],
            ownerName: $data['OwnerName'],
            sickTitle: $data['Seek']
        );
    }
}

