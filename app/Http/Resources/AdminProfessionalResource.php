<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminProfessionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Professional $professional */
        $professional = $this->resource;

        return [
            'id' => $professional->id,
            'name' => $professional->name,
            'active' => (bool) $professional->active,
            'services' => $this->whenLoaded('services', fn (): array => $professional->services
                ->map(fn (Service $service): array => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'active' => (bool) $service->active,
                ])
                ->values()
                ->all()),
            'schedules_count' => $this->whenCounted('schedules'),
            'appointments_count' => $this->whenCounted('appointments'),
            'created_at' => $professional->created_at?->toISOString(),
            'updated_at' => $professional->updated_at?->toISOString(),
        ];
    }
}
