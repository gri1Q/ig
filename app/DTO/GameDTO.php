<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameDTO
{
    public function __construct(
        public string $provider,
        public string $externalId,
        public string $title,
        public string $category,
        public ?float $rtp,
        public string $createdAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'external_id' => $this->externalId,
            'title' => $this->title,
            'category' => $this->category,
            'rtp' => $this->rtp,
            'created_at' => $this->createdAt,
        ];
    }
}
