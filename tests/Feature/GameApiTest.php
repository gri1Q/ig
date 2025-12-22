<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест проверяет, что повторный импорт не создаёт дубликаты
     * и корректно обновляет существующие записи.
     */
    public function test_import_is_idempotent_and_updates_existing_records()
    {
        // 1. Первый импорт - создание записей
        $gamesData = [
            [
                'id' => 'game_1',
                'title' => 'Book of Dead',
                'category' => 'slots',
                'active' => true,
                'rtp' => 96.5,
            ],
            [
                'id' => 'game_2',
                'title' => 'Roulette European',
                'category' => 'table',
                'active' => true,
            ],
        ];

        $response1 = $this->postJson('/api/v1/providers/netent/games/import', $gamesData);
        $response1->assertStatus(200);
        $response1->assertJson([
            'provider' => 'netent',
            'received' => 2,
            'created' => 2,
            'updated' => 0,
            'skipped' => 0,
        ]);

        // Проверяем, что записи созданы
        $this->assertDatabaseCount('games', 2);
        $this->assertDatabaseHas('games', [
            'provider' => 'netent',
            'external_id' => 'game_1',
            'title' => 'Book of Dead',
            'rtp' => 96.50,
        ]);

        // 2. Второй импорт с обновленными данными для существующей игры
        $updatedGamesData = [
            [
                'id' => 'game_1', // Та же игра
                'title' => 'Book of Dead UPDATED', // Измененный заголовок
                'category' => 'slots',
                'active' => false, // Изменен статус
                'rtp' => 97.0, // Изменен RTP
            ],
            [
                'id' => 'game_3', // Новая игра
                'title' => 'Blackjack',
                'category' => 'table',
                'active' => true,
            ],
        ];

        $response2 = $this->postJson('/api/v1/providers/netent/games/import', $updatedGamesData);
        $response2->assertStatus(200);
        $response2->assertJson([
            'provider' => 'netent',
            'received' => 2,
            'created' => 1, // game_3 создана
            'updated' => 1, // game_1 обновлена
            'skipped' => 0,
        ]);

        // Проверяем, что дубликатов нет (всего 3 игры: game_1, game_2, game_3)
        $this->assertDatabaseCount('games', 3);

        // Проверяем обновление game_1
        $this->assertDatabaseHas('games', [
            'provider' => 'netent',
            'external_id' => 'game_1',
            'title' => 'Book of Dead UPDATED', // Проверяем обновление
            'is_active' => false, // Проверяем обновление статуса
            'rtp' => 97.00, // Проверяем обновление RTP
        ]);

        // Проверяем, что game_2 осталась без изменений
        $this->assertDatabaseHas('games', [
            'provider' => 'netent',
            'external_id' => 'game_2',
            'title' => 'Roulette European', // Оригинальное название
        ]);

        // Проверяем создание game_3
        $this->assertDatabaseHas('games', [
            'provider' => 'netent',
            'external_id' => 'game_3',
            'title' => 'Blackjack',
        ]);
    }

    /**
     * Тест проверяет, что GET /api/games возвращает только активные игры
     * и поддерживает фильтрацию по provider.
     */
    public function test_get_active_games_returns_only_active_and_supports_provider_filter()
    {
        // Создаем тестовые данные:
        // Активные игры от netent
        Game::factory()->create([
            'provider' => 'netent',
            'external_id' => 'netent_1',
            'title' => 'Netent Slot 1',
            'category' => 'slots',
            'is_active' => true,
        ]);

        Game::factory()->create([
            'provider' => 'netent',
            'external_id' => 'netent_2',
            'title' => 'Netent Slot 2',
            'category' => 'slots',
            'is_active' => true,
        ]);

        // Неактивная игра от netent
        Game::factory()->create([
            'provider' => 'netent',
            'external_id' => 'netent_inactive',
            'title' => 'Netent Inactive',
            'category' => 'slots',
            'is_active' => false,
        ]);

        // Активная игра от другого провайдера
        Game::factory()->create([
            'provider' => 'pragmatic',
            'external_id' => 'pragmatic_1',
            'title' => 'Pragmatic Slot',
            'category' => 'slots',
            'is_active' => true,
        ]);

        // Неактивная игра от другого провайдера
        Game::factory()->create([
            'provider' => 'pragmatic',
            'external_id' => 'pragmatic_inactive',
            'title' => 'Pragmatic Inactive',
            'category' => 'slots',
            'is_active' => false,
        ]);

        //Тестируем без фильтра - должны вернуться только активные игры
        $response1 = $this->getJson('/api/v1/games');
        $response1->assertStatus(200);

        $response1->assertJsonStructure([
            'data' => [
                '*' => ['provider', 'external_id', 'title', 'category', 'rtp', 'created_at']
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page']
        ]);

        $response1->assertJsonCount(3, 'data'); // netent_1, netent_2, pragmatic_1

        // Проверяем, что в ответе нет неактивных игр
        $data = $response1->json('data');
        foreach ($data as $game) {
            $this->assertNotEquals('netent_inactive', $game['external_id']);
            $this->assertNotEquals('pragmatic_inactive', $game['external_id']);
        }

        // Тестируем с фильтром по provider=netent - должны вернуться только активные игры netent (2 штуки)
        $response2 = $this->getJson('/api/v1/games?provider=netent');
        $response2->assertStatus(200);
        $response2->assertJsonCount(2, 'data'); // netent_1, netent_2

        $data2 = $response2->json('data');
        foreach ($data2 as $game) {
            $this->assertEquals('netent', $game['provider']);
        }

        // Тестируем с фильтром по provider=pragmatic - должна вернуться 1 активная игра
        $response3 = $this->getJson('/api/v1/games?provider=pragmatic');
        $response3->assertStatus(200);
        $response3->assertJsonCount(1, 'data'); // pragmatic_1

        $data3 = $response3->json('data');
        $this->assertEquals('pragmatic', $data3[0]['provider']);
        $this->assertEquals('pragmatic_1', $data3[0]['external_id']);

        // Проверяем фильтр по несуществующему провайдеру - должен вернуть пустой список
        $response4 = $this->getJson('/api/v1/games?provider=unknown');
        $response4->assertStatus(422);
        $response4->assertJsonValidationErrors(['provider']);
    }
}
