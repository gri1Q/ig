<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Определяет допустимые категории игр в системе.
 */
enum GameCategory: string
{
    case SLOTS = "slots";
    case LIVE = "live";
    case TABLE = "table";

}
