<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminProductCategoryRequest extends FormRequest
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
        /** @var ProductCategory|null $category */
        $category = $this->route('productCategory');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($category?->id)],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('product_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
