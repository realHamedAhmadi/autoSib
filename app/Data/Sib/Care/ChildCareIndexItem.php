<?php

namespace App\Data\Sib\Care;

final readonly class ChildCareIndexItem
{
    public function __construct(
        public int $id,
        public string $title,
        public ?int $menuIndexId,
        public ?int $basicVisitId,
        public ?string $dateVisit,
        public ?string $timeVisit,
        public string $hash,
        public string $action,
    ) {
    }

    public static function fromApiResponse(array $item): self
    {
        return new self(
            id: (int) ($item['Id'] ?? 0),
            title: (string) ($item['Title'] ?? ''),
            menuIndexId: isset($item['Id_MenuIndex']) ? (int) $item['Id_MenuIndex'] : null,
            basicVisitId: isset($item['Id_BasicVisit']) ? (int) $item['Id_BasicVisit'] : null,
            dateVisit: isset($item['DateVisit']) ? (string) $item['DateVisit'] : null,
            timeVisit: isset($item['TimeVisit']) ? (string) $item['TimeVisit'] : null,
            hash: (string) ($item['Hash'] ?? ''),
            action: (string) ($item['Action'] ?? ''),
        );
    }

    public function isPending(): bool
    {
        return $this->action === 'Create';
    }

    public function isCompleted(): bool
    {
        return $this->action === 'Repeat';
    }
}
