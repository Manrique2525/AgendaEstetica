<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaAppointmentDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Appointment $appointment */
        $appointment = $this->resource;
        /** @var Customer $customer */
        $customer = $appointment->customer;
        /** @var Service $service */
        $service = $appointment->service;
        /** @var ServiceCategory|null $category */
        $category = $service->category;

        return [
            ...(new AdminAgendaAppointmentResource($appointment))->toArray($request),
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ],
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $category === null ? null : [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ],
            'history' => AdminAgendaAppointmentHistoryResource::collection($appointment->history),
        ];
    }
}
