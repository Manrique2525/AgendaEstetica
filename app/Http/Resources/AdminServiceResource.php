<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class AdminServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Service $service */
        $service = $this->resource;
        /** @var ServiceCategory|null $category */
        $category = $service->category;

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'image_url' => $service->image_path === null ? null : Storage::disk('public')->url($service->image_path),
            'duration_minutes' => $service->duration_minutes,
            'pricing_type' => (string) $service->getRawOriginal('pricing_type'),
            'price' => $service->price,
            'active' => (bool) $service->active,
            'category' => $category === null ? null : [
                'id' => $category->id,
                'name' => $category->name,
                'active' => (bool) $category->active,
            ],
            'appointments_count' => $this->whenCounted('appointments'),
            'created_at' => $service->created_at?->toISOString(),
            'updated_at' => $service->updated_at?->toISOString(),
        ];
    }
}
