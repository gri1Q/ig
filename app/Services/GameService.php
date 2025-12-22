<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\GameDTO;
use App\DTO\GameImportDTO;
use App\DTO\GameListQueryDTO;
use App\DTO\GetActiveGamesDTO;
use App\DTO\ImportGamesDTO;
use App\Enums\GameCategory;
use App\Models\Game;
use App\Repositories\GameRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GameService
{

    public function __construct(private GameRepository $gameRepository)
    {
    }

    /**
     * Получить активные игры с фильтрацией и сортировкой.
     *
     * @param GameListQueryDTO $query
     * @return GetActiveGamesDTO
     */
    public function getActiveGames(GameListQueryDTO $query): GetActiveGamesDTO
    {
        $paginator = $this->gameRepository->getActiveGamesPaginate($query);
        $gameDTOs = [];
        /** @var Game $game */
        foreach ($paginator->items() as $game) {
            $gameDTOs[] = new GameDTO(
                $game->provider,
                $game->external_id,
                $game->title,
                $game->category,
                $game->rtp !== null ? (float)$game->rtp : null,
                $game->created_at->toDateTimeString(),
            );
        }

        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];

        // Преобразуем в DTO для ответа
        return new GetActiveGamesDTO($gameDTOs, $meta);
    }


    /**
     * Импорт игр от провайдера.
     *
     * @param string $provider
     * @param array $gamesData
     * @return ImportGamesDTO
     */
    public function importGames(string $provider, array $gamesData): ImportGamesDTO
    {
        $received = count($gamesData);
        $errors = [];
        $validGames = [];
        $externalIdsInRequest = [];

        //Валидация и подготовка данных
        foreach ($gamesData as $index => $gameData) {
            try {
                $validationResult = $this->validateAndPrepareGame($gameData, $index, $externalIdsInRequest);

                if ($validationResult['is_valid']) {
                    $validGames[] = $validationResult['game'];
                } else {
                    $errors[] = $validationResult['error'];
                }
            } catch (Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'message' => 'Ошибка обработки игры: ' . $e->getMessage(),
                ];
            }
        }

        $skipped = count($errors);

        if (empty($validGames)) {
            return new ImportGamesDTO($provider, $received, 0, 0, $skipped, $errors);
        }

        return $this->processValidGames($provider, $validGames, $received, $skipped, $errors);
    }

    /**
     * Обработка игр в транзакции.
     */
    private function processValidGames(
        string $provider,
        array $validGames,
        int $received,
        int $skipped,
        array $errors
    ): ImportGamesDTO {
        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            $externalIds = array_map(fn(GameImportDTO $dto) => $dto->externalID, $validGames);
            $existingExternalIds = $this->gameRepository->getExistingExternalIDs($provider, $externalIds)->pluck(
                'external_id'
            )->toArray();

            $rowsToUpsert = [];
            $now = now();

            foreach ($validGames as $dto) {
                $row = [
                    'provider' => $provider,
                    'external_id' => $dto->externalID,
                    'title' => $dto->title,
                    'category' => $dto->category,
                    'is_active' => $dto->isActive,
                    'rtp' => $dto->rtp,
                    'updated_at' => $now,
                ];

                if (!in_array($dto->externalID, $existingExternalIds, true)) {
                    $row['created_at'] = $now;
                }

                $rowsToUpsert[] = $row;
            }

            $this->gameRepository->bulkUpsert($rowsToUpsert);

            // Подсчитываем статистику
            $created = count(array_diff($externalIds, $existingExternalIds));
            $updated = count($externalIds) - $created;

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $errors[] = [
                'index' => null,
                'message' => 'Ошибка при пакетной обработке: ' . $e->getMessage()
            ];

            $skipped = $received;
        }

        return new ImportGamesDTO($provider, $received, $created, $updated, $skipped, $errors);
    }

    /**
     * Валидирует и подготавливает одну игру.
     *
     * @param array $gameData
     * @param int $index
     * @param array $seenExternalIds
     * @return array
     */
    private function validateAndPrepareGame(array $gameData, int $index, array &$seenExternalIds): array
    {
        $validator = Validator::make($gameData, [
            'id' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, GameCategory::cases()))],
            'active' => ['required', 'boolean'],
            'rtp' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return [
                'is_valid' => false,
                'error' => [
                    'index' => $index,
                    'message' => $validator->errors()->first()
                ]
            ];
        }

        $externalID = (string)$gameData['id'];

        // Проверка на дубликаты в текущем запросе
        if (isset($seenExternalIds[$externalID])) {
            $firstIndex = $seenExternalIds[$externalID];
            return [
                'is_valid' => false,
                'error' => [
                    'index' => $index,
                    'message' => "Дубликат external_id '{$externalID}' (первое вхождение в позиции {$firstIndex})"
                ]
            ];
        }

        $seenExternalIds[$externalID] = $index;

        $dto = new GameImportDTO(
            $externalID,
            $gameData['title'],
            $gameData['category'],
            (bool)$gameData['active'],
            isset($gameData['rtp']) ? (float)$gameData['rtp'] : null
        );

        return [
            'is_valid' => true,
            'game' => $dto
        ];
    }
}
