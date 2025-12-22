<?php

declare(strict_types=1);

use App\Http\Controllers\Api\v1\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        Route::get('/games', [GameController::class, 'getActiveGames']);
        Route::post('/providers/{provider}/games/import', [GameController::class, 'import']);
    });

