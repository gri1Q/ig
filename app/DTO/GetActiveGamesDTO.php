<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class GetActiveGamesDTO
{
    public function __construct(
        public array $games,
        public array $meta
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn(GameDTO $game) => $game->toArray(),
                $this->games
            ),
            'meta' => $this->meta,
        ];
    }
}
