<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\DTO\GameListQueryDTO;
use App\Enums\GameProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetActiveGamesRequest;
use App\Services\GameService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(protected GameService $gameService)
    {
    }

    /**
     * Получить список активных игр.
     *
     * Поддерживает query-параметры:
     * - provider (например: ?provider=netent)
     * - category (например: ?category=slots)
     * - sort (title|created_at)
     * - order (asc|desc, по умолчанию asc)
     * - per_page (по умолчанию 20)
     *
     * @param GetActiveGamesRequest $request
     * @return JsonResponse
     */
    public function getActiveGames(GetActiveGamesRequest $request): JsonResponse
    {
        $queryDTO = $this->createQueryDTO($request->validated());
        $responseDTO = $this->gameService->getActiveGames($queryDTO);

        return response()->json($responseDTO->toArray());
    }

    /**
     * Импорт игр.
     *
     * @param string $provider
     * @param Request $request
     * @return JsonResponse
     */
    public function import(string $provider, Request $request): JsonResponse
    {
        if (!GameProvider::isValid($provider)) {
            return response()->json([
                'error' => 'Provider failed',
                'message' => "Такого провайдера нет",
            ], 400);
        }

        $json = $request->getContent();
        $gamesData = json_decode($json, true);

        try {
            $result = $this->gameService->importGames($provider, $gamesData);
            return response()->json($result->toArray());
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Import failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Метод, который превращает пустые строки в null.
     *
     * @param string|null $value
     * @return string|null
     */
    private function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Проверка поля сортировки. Разрешены только указанные значения.
     *
     * @param string $sort
     * @return string
     */
    private function normalizeSort(string $sort): string
    {
        $allowed = ['title', 'created_at'];
        $sort = trim($sort);
        return in_array($sort, $allowed, true) ? $sort : 'title';
    }

    /**
     * Проверка направления сортировки — разрешено только asc или desc.
     *
     * @param string $order
     * @return string
     */
    private function normalizeOrder(string $order): string
    {
        $order = strtolower(trim($order));
        return $order === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @param array $validatedData
     * @return GameListQueryDTO
     */
    private function createQueryDTO(array $validatedData): GameListQueryDTO
    {
        return new GameListQueryDTO(
            $this->normalizeString($validatedData['provider'] ?? null),
            $this->normalizeString($validatedData['category'] ?? null),
            $this->normalizeSort($validatedData['sort'] ?? 'title'),
            $this->normalizeOrder($validatedData['order'] ?? 'asc'),
            (int)($validatedData['per_page'] ?? 20),
            (int)($validatedData['page'] ?? 1)
        );
    }
}
