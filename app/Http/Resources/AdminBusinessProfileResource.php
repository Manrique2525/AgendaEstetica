<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminBusinessProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BusinessProfile $profile */
        $profile = $this->resource;

        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'phone' => $profile->phone,
            'timezone' => $profile->timezone,
            'max_simultaneous_clients' => (int) $profile->max_simultaneous_clients,
            'hours' => $this->whenLoaded('hours', fn (): array => $profile->hours
                ->sortBy([['weekday', 'asc'], ['interval_order', 'asc']])
                ->values()
                ->map(fn (BusinessHour $hour): array => [
                    'id' => $hour->id,
                    'weekday' => (int) $hour->weekday,
                    'interval_order' => (int) $hour->interval_order,
                    'opens_at' => (string) $hour->opens_at,
                    'closes_at' => (string) $hour->closes_at,
                ])
                ->all()),
            'created_at' => $profile->created_at?->toISOString(),
            'updated_at' => $profile->updated_at?->toISOString(),
        ];
    }
}
