<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProductCategory $category */
        $category = $this->resource;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'active' => (bool) $category->active,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }
}
