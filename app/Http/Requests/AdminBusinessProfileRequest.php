<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AdminBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:32'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
            'max_simultaneous_clients' => ['required', 'integer', 'min:1', 'max:100000'],
            'hours' => ['sometimes', 'array'],
            'hours.*.weekday' => ['required', 'integer', 'between:1,7'],
            'hours.*.opens_at' => ['required', 'date_format:H:i'],
            'hours.*.closes_at' => ['required', 'date_format:H:i'],
        ];
    }
}
