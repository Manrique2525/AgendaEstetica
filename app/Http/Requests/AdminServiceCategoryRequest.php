<?php

namespace App\Http\Requests;

use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class AdminServiceCategoryRequest extends FormRequest
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
        /** @var ServiceCategory|null $category */
        $category = $this->route('serviceCategory');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('service_categories', 'name')->ignore($category?->id)],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('service_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (trim((string) $this->input('name')) === '') {
                $validator->errors()->add('name', 'El nombre es obligatorio.');
            }
        });
    }
}
