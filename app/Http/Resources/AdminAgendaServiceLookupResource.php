<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaServiceLookupResource extends JsonResource
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
            'duration_minutes' => $service->duration_minutes,
            'pricing_type' => (string) $service->getRawOriginal('pricing_type'),
            'price' => $service->price,
            'category' => $category === null ? null : [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ];
    }
}
