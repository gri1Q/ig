<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\GameListQueryDTO;
use App\Enums\OperationStatus;
use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GameRepository
{


    /**
     * Получить активные игры с фильтрацией и сортировкой.
     *
     * @param GameListQueryDTO $query
     * @return LengthAwarePaginator
     */
    public function getActiveGamesPaginate(GameListQueryDTO $query): LengthAwarePaginator
    {
        $activeGame = Game::query()->where('is_active', true);

        if ($query->provider !== null) {
            $activeGame->where('provider', $query->provider);
        }

        if ($query->category !== null) {
            $activeGame->where('category', $query->category);
        }

        return $activeGame->orderBy($query->sortField, $query->sortDirection)->paginate(
            $query->perPage,
            ['*'],
            'page',
            $query->page
        );
    }


    /**
     * Получить существующие external_id для провайдера.
     *
     * @param string $provider
     * @param array $externalIds
     * @return Collection
     */
    public function getExistingExternalIDs(string $provider, array $externalIds): Collection
    {
        return Game::query()->where('provider', $provider)
            ->whereIn('external_id', $externalIds)
            ->get();
    }

    /**
     * Массовый upsert игр.
     *
     * @param array $rowsToUpsert
     * @return int
     */
    public function bulkUpsert(array $rowsToUpsert): int
    {
        return Game::query()->upsert(
            $rowsToUpsert,
            ['provider', 'external_id'],
            ['title', 'category', 'is_active', 'rtp', 'updated_at']
        );
    }

}
