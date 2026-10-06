<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListAdminAgendaAppointments;
use App\Http\Requests\AdminAgendaAppointmentIndexRequest;
use App\Http\Resources\AdminAgendaAppointmentDetailResource;
use App\Http\Resources\AdminAgendaAppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminAgendaAppointmentController
{
    public function index(
        AdminAgendaAppointmentIndexRequest $request,
        ListAdminAgendaAppointments $listAppointments,
    ): AnonymousResourceCollection {
        return AdminAgendaAppointmentResource::collection($listAppointments->execute($request->validated()));
    }

    public function show(Appointment $appointment): AdminAgendaAppointmentDetailResource
    {
        $appointment->load([
            'customer',
            'service.category',
            'professional',
            'history' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
        ]);

        return new AdminAgendaAppointmentDetailResource($appointment);
    }
}
