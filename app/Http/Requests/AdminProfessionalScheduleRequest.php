<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AdminProfessionalScheduleRequest extends FormRequest
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
            'intervals' => ['present', 'array'],
            'intervals.*.weekday' => ['required', 'integer', 'between:1,7'],
            'intervals.*.starts_at' => ['required', 'date_format:H:i'],
            'intervals.*.ends_at' => ['required', 'date_format:H:i'],
        ];
    }
}
