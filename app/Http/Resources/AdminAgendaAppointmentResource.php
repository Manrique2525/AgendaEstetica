<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Appointment $appointment */
        $appointment = $this->resource;
        /** @var Customer $customer */
        $customer = $appointment->customer;
        /** @var Service $service */
        $service = $appointment->service;
        /** @var Professional $professional */
        $professional = $appointment->professional;
        /** @var CarbonInterface|null $startsAt */
        $startsAt = $appointment->starts_at;
        /** @var CarbonInterface|null $endsAt */
        $endsAt = $appointment->ends_at;

        return [
            'id' => $appointment->id,
            'status' => (string) $appointment->getRawOriginal('status'),
            'starts_at' => $startsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'ends_at' => $endsAt?->copy()->setTimezone('UTC')->toIso8601String(),
            'duration_minutes' => $appointment->duration_minutes,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
            ],
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
            ],
            'professional' => [
                'id' => $professional->id,
                'name' => $professional->name,
            ],
        ];
    }
}
