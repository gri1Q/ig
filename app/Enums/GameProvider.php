<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Определяет допустимые провайдеры игр в системе.
 */
enum GameProvider: string
{
    case NETENT = "netent";
    case PRAGMATIC = "pragmatic";

    /**
     * Валидация провайдера.
     *
     * @param string $provider
     * @return bool
     */
    public static function isValid(string $provider): bool
    {
        return self::tryFrom($provider) !== null;
    }
}
