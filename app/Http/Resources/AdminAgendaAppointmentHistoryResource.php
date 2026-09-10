<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AppointmentHistory;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaAppointmentHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AppointmentHistory $history */
        $history = $this->resource;
        /** @var CarbonInterface|null $oldStartsAt */
        $oldStartsAt = $history->old_starts_at;
        /** @var CarbonInterface|null $oldEndsAt */
        $oldEndsAt = $history->old_ends_at;
        /** @var CarbonInterface|null $newStartsAt */
        $newStartsAt = $history->new_starts_at;
        /** @var CarbonInterface|null $newEndsAt */
        $newEndsAt = $history->new_ends_at;
        /** @var CarbonInterface|null $createdAt */
        $createdAt = $history->created_at;

        return [
            'id' => $history->id,
            'event_type' => (string) $history->getRawOriginal('event_type'),
            'from_status' => $history->getRawOriginal('from_status'),
            'to_status' => $history->getRawOriginal('to_status'),
            'old_starts_at' => $oldStartsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'old_ends_at' => $oldEndsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'new_starts_at' => $newStartsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'new_ends_at' => $newEndsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'old_professional_id' => $history->old_professional_id,
            'new_professional_id' => $history->new_professional_id,
            'created_at' => $createdAt?->copy()->setTimezone('UTC')->toIso8601String(),
        ];
    }
}
