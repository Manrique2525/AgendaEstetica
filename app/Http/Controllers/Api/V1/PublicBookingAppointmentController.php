<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreatePublicBooking;
use App\Exceptions\PublicBookingIdempotencyConflict;
use App\Exceptions\PublicBookingIdempotencyInProgress;
use App\Exceptions\PublicBookingRateLimitExceeded;
use App\Http\Requests\PublicBookingAppointmentRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class PublicBookingAppointmentController
{
    public function store(
        PublicBookingAppointmentRequest $request,
        CreatePublicBooking $createPublicBooking,
    ): JsonResponse {
        try {
            $data = $request->validated();
            $result = $createPublicBooking->execute([
                'service_id' => (int) $data['service_id'],
                'professional_id' => (int) $data['professional_id'],
                'starts_at' => $data['starts_at'],
                'name' => $data['name'],
                'phone' => $data['phone'],
            ], $data['idempotency_key']);
        } catch (PublicBookingIdempotencyConflict) {
            return response()->json([
                'message' => 'La clave de idempotencia ya fue utilizada para otra solicitud.',
                'code' => 'idempotency_key_conflict',
            ], 409);
        } catch (PublicBookingIdempotencyInProgress) {
            return response()->json([
                'message' => 'La solicitud de reserva sigue en proceso. Intenta nuevamente.',
                'code' => 'idempotency_request_in_progress',
            ], 409);
        } catch (PublicBookingRateLimitExceeded) {
            return response()->json([
                'message' => 'No pudimos procesar tantas solicitudes. Intenta más tarde.',
                'code' => 'too_many_requests',
            ], 429);
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'El horario seleccionado ya no está disponible.',
                'code' => 'appointment_unavailable',
            ], 409);
        }

        return response()->json(['data' => $result['response']], 201);
    }
}
