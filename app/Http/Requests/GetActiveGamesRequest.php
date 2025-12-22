<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\GameCategory;
use App\Enums\GameProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetActiveGamesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider' => ['nullable', 'string',Rule::in(array_map(fn($c) => $c->value, GameProvider::cases()))],
            'category' => ['nullable', 'string', Rule::in(array_map(fn($c) => $c->value, GameCategory::cases()))],
            'sort' => ['nullable', 'string'],
            'order' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
