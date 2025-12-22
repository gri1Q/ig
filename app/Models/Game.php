<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Модель Game представляет игру в каталоге iGaming агрегатора.
 *
 * @property int $id                        Уникальный идентификатор игры
 * @property string $provider               Провайдер игры (например: netent, pragmatic)
 * @property string $external_id            Идентификатор игры у провайдера
 * @property string $title                  Название игры
 * @property string $category               Категория: slots, live, table
 * @property bool $is_active                Активна ли игра для показа в каталоге
 * @property float|null $rtp                Процент возврата (Return to Player), например 96.50
 * @property Carbon $created_at             Дата создания записи
 * @property Carbon $updated_at             Дата обновления записи
 */
class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'external_id',
        'title',
        'category',
        'is_active',
        'rtp',
    ];
}
