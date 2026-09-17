<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicBookingAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource['date'],
            'timezone' => $this->resource['timezone'],
            'slots' => $this->resource['slots'],
        ];
    }
}
