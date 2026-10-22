<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ServiceCategory $category */
        $category = $this->resource;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'active' => (bool) $category->active,
            'services_count' => $this->whenCounted('services'),
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }
}
