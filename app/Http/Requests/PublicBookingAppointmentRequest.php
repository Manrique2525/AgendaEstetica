<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\CustomerPhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class PublicBookingAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'min:1'],
            'professional_id' => ['required', 'integer', 'min:1'],
            'starts_at' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (trim((string) $this->input('name')) === '') {
                $validator->errors()->add('name', 'The name field is required.');
            }

            try {
                (new CustomerPhoneNormalizer)->normalize((string) $this->input('phone'));
            } catch (\InvalidArgumentException) {
                $validator->errors()->add('phone', 'The phone number is invalid.');
            }
        });
    }
}
