<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ServicePricingType;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminServiceRequest extends FormRequest
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
        /** @var Service|null $service */
        $service = $this->route('service');

        $isVariable = $this->input('pricing_type') === ServicePricingType::VARIABLE->value;

        return [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('services', 'slug')->ignore($service?->id)],
            'description' => ['nullable', 'string', 'max:3000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'pricing_type' => ['required', Rule::in(array_column(ServicePricingType::cases(), 'value'))],
            'price' => $isVariable
                ? ['nullable', 'prohibited', 'numeric', 'min:0', 'max:999999.99']
                : ['required', 'numeric', 'min:0', 'max:999999.99'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
