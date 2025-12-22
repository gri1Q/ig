<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GameListQueryDTO
{
    public function __construct(
        public ?string $provider,
        public ?string $category,
        public string $sortField = 'title',
        public string $sortDirection = 'asc',
        public int $perPage = 20,
        public int $page = 1
    ) {
    }

}
