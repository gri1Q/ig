<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameImportDTO
{
    public function __construct(
        public string $externalID,
        public string $title,
        public string $category,
        public bool $isActive,
        public ?float $rtp = null
    ) {}

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'external_id' => $this->externalID,
            'title' => $this->title,
            'category' => $this->category,
            'is_active' => $this->isActive,
            'rtp' => $this->rtp,
        ];
    }
}
