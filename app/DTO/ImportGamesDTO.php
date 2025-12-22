<?php

declare(strict_types=1);

namespace App\DTO;

final class ImportGamesDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly int $received,
        public readonly int $created,
        public readonly int $updated,
        public readonly int $skipped,
        public readonly array $errors
    ) {}

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'received' => $this->received,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
