<?php

namespace Database\Seeders;

use App\Enums\GameCategory;
use App\Enums\GameProvider;
use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $games = [
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_001',
                'title' => 'Starburst',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.10
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_002',
                'title' => 'Gonzo’s Quest',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 95.97
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_003',
                'title' => 'Live Blackjack',
                'category' => GameCategory::LIVE->value,
                'is_active' => true,
                'rtp' => null
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_001',
                'title' => 'Sweet Bonanza',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.51
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_002',
                'title' => 'Wolf Gold',
                'category' => GameCategory::SLOTS->value,
                'is_active' => false,
                'rtp' => 94.00
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_003',
                'title' => 'Roulette',
                'category' => GameCategory::TABLE->value,
                'is_active' => true,
                'rtp' => null
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_004',
                'title' => 'Dead or Alive',
                'category' => GameCategory::SLOTS->value,
                'is_active' => false,
                'rtp' => 94.00
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_004',
                'title' => 'The Dog House',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.55
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_005',
                'title' => 'Jack and the Beanstalk',
                'category' => GameCategory::SLOTS->value,
                'is_active' => false,
                'rtp' => null
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_005',
                'title' => 'Great Rhino',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.53
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_006',
                'title' => 'Mega Fortune',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.00
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_006',
                'title' => 'Gates of Olympus',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.50
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_007',
                'title' => 'Live Roulette',
                'category' => GameCategory::LIVE->value,
                'is_active' => true,
                'rtp' => 97.30
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_007',
                'title' => 'Blackjack',
                'category' => GameCategory::TABLE->value,
                'is_active' => false,
                'rtp' => null
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_008',
                'title' => 'Blood Suckers',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 98.00
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_008',
                'title' => 'Buffalo King',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.52
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_009',
                'title' => 'Live Baccarat',
                'category' => GameCategory::LIVE->value,
                'is_active' => true,
                'rtp' => null
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_009',
                'title' => 'Sugar Rush',
                'category' => GameCategory::SLOTS->value,
                'is_active' => true,
                'rtp' => 96.50
            ],
            [
                'provider' => GameProvider::NETENT->value,
                'external_id' => 'net_010',
                'title' => 'European Roulette',
                'category' => GameCategory::TABLE->value,
                'is_active' => true,
                'rtp' => 97.30
            ],
            [
                'provider' => GameProvider::PRAGMATIC->value,
                'external_id' => 'prag_010',
                'title' => 'Wild West Gold',
                'category' => GameCategory::SLOTS->value,
                'is_active' => false,
                'rtp' => 96.51
            ],
        ];

        foreach ($games as $game) {
            Game::create($game);
        }
    }
}
