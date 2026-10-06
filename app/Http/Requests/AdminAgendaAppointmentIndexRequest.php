<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use App\Support\AdminAgendaRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class AdminAgendaAppointmentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d'],
            'professional_id' => ['sometimes', 'integer', 'exists:professionals,id'],
            'status' => ['sometimes', Rule::enum(AppointmentStatus::class)],
            'service_id' => ['sometimes', 'integer', 'exists:services,id'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                (new AdminAgendaRange)->resolve((string) $this->input('from'), (string) $this->input('to'));
            } catch (\InvalidArgumentException $exception) {
                $validator->errors()->add('from', $exception->getMessage());
            }
        });
    }
}
