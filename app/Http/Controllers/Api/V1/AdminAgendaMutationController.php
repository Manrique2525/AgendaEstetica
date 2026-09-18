<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CancelAppointment;
use App\Actions\CompleteAppointment;
use App\Actions\CreateAppointment;
use App\Actions\MarkAppointmentNoShow;
use App\Actions\RescheduleAppointment;
use App\Http\Requests\AdminAgendaCreateAppointmentRequest;
use App\Http\Requests\AdminAgendaRescheduleAppointmentRequest;
use App\Http\Resources\AdminAgendaAppointmentDetailResource;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class AdminAgendaMutationController
{
    public function store(
        AdminAgendaCreateAppointmentRequest $request,
        CreateAppointment $createAppointment,
    ): JsonResponse {
        $data = $request->validated();

        try {
            $appointment = $createAppointment->execute(
                Customer::query()->findOrFail($data['customer_id']),
                Service::query()->findOrFail($data['service_id']),
                Professional::query()->findOrFail($data['professional_id']),
                CarbonImmutable::parse($data['starts_at'])->utc(),
                CarbonImmutable::parse($data['ends_at'])->utc(),
            );
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'El horario seleccionado ya no está disponible.',
                'code' => 'appointment_unavailable',
            ], 409);
        }

        return (new AdminAgendaAppointmentDetailResource($this->loadDetail($appointment)))
            ->response()
            ->setStatusCode(201);
    }

    public function reschedule(
        AdminAgendaRescheduleAppointmentRequest $request,
        Appointment $appointment,
        RescheduleAppointment $rescheduleAppointment,
    ): AdminAgendaAppointmentDetailResource|JsonResponse {
        $data = $request->validated();

        try {
            $updated = $rescheduleAppointment->execute(
                $appointment,
                Professional::query()->findOrFail($data['professional_id']),
                CarbonImmutable::parse($data['starts_at'])->utc(),
                CarbonImmutable::parse($data['ends_at'])->utc(),
            );
        } catch (InvalidArgumentException $exception) {
            $isStateConflict = str_starts_with($exception->getMessage(), 'Only confirmed');

            return response()->json([
                'message' => $isStateConflict
                    ? 'La cita ya no puede reprogramarse en su estado actual.'
                    : 'El horario seleccionado ya no está disponible.',
                'code' => $isStateConflict ? 'appointment_state_conflict' : 'appointment_unavailable',
            ], 409);
        }

        return new AdminAgendaAppointmentDetailResource($this->loadDetail($updated));
    }

    public function cancel(
        Appointment $appointment,
        CancelAppointment $cancelAppointment,
    ): AdminAgendaAppointmentDetailResource|JsonResponse {
        try {
            $updated = $cancelAppointment->execute($appointment);
        } catch (InvalidArgumentException) {
            return $this->stateConflictResponse();
        }

        return new AdminAgendaAppointmentDetailResource($this->loadDetail($updated));
    }

    public function complete(
        Appointment $appointment,
        CompleteAppointment $completeAppointment,
    ): AdminAgendaAppointmentDetailResource|JsonResponse {
        try {
            $updated = $completeAppointment->execute($appointment);
        } catch (InvalidArgumentException) {
            return $this->stateConflictResponse();
        }

        return new AdminAgendaAppointmentDetailResource($this->loadDetail($updated));
    }

    public function noShow(
        Appointment $appointment,
        MarkAppointmentNoShow $markAppointmentNoShow,
    ): AdminAgendaAppointmentDetailResource|JsonResponse {
        try {
            $updated = $markAppointmentNoShow->execute($appointment);
        } catch (InvalidArgumentException) {
            return $this->stateConflictResponse();
        }

        return new AdminAgendaAppointmentDetailResource($this->loadDetail($updated));
    }

    private function stateConflictResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'La cita ya no puede procesarse en su estado actual.',
            'code' => 'appointment_state_conflict',
        ], 409);
    }

    private function loadDetail(Appointment $appointment): Appointment
    {
        return $appointment->load([
            'customer',
            'service.category',
            'professional',
            'history' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
        ]);
    }
}
