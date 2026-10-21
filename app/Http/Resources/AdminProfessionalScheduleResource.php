<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProfessionalSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminProfessionalScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfessionalSchedule $schedule */
        $schedule = $this->resource;

        return [
            'id' => $schedule->id,
            'weekday' => (int) $schedule->weekday,
            'starts_at' => substr((string) $schedule->starts_at, 0, 5),
            'ends_at' => substr((string) $schedule->ends_at, 0, 5),
        ];
    }
}
