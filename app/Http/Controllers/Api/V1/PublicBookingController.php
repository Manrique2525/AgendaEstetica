<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListPublicBookableTimes;
use App\Http\Requests\PublicBookingAvailabilityRequest;
use App\Http\Requests\PublicBookingProfessionalIndexRequest;
use App\Http\Resources\PublicBookingAvailabilityResource;
use App\Http\Resources\PublicBookingContextResource;
use App\Http\Resources\PublicBookingProfessionalResource;
use App\Http\Resources\PublicBookingServiceResource;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

final class PublicBookingController
{
    public function context(): PublicBookingContextResource
    {
        return new PublicBookingContextResource(BusinessProfile::query()
            ->where('singleton_key', 1)
            ->firstOrFail());
    }

    public function services(Request $request): AnonymousResourceCollection
    {
        return PublicBookingServiceResource::collection(Service::query()
            ->with('category:id,name')
            ->where('active', true)
            ->whereHas('category', fn ($query) => $query->where('active', true))
            ->orderBy('service_category_id')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'service_category_id', 'name', 'duration_minutes', 'pricing_type', 'price']));
    }

    public function professionals(PublicBookingProfessionalIndexRequest $request): AnonymousResourceCollection
    {
        $serviceId = $request->integer('service_id');

        return PublicBookingProfessionalResource::collection(Professional::query()
            ->where('active', true)
            ->whereHas('services', fn ($query) => $query
                ->whereKey($serviceId)
                ->where('active', true)
                ->whereHas('category', fn ($category) => $category->where('active', true)))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']));
    }

    public function availability(
        PublicBookingAvailabilityRequest $request,
        ListPublicBookableTimes $listBookableTimes,
    ): PublicBookingAvailabilityResource|JsonResponse {
        $data = $request->validated();
        try {
            $result = $listBookableTimes->execute(
                (int) $data['service_id'],
                (int) $data['professional_id'],
                $data['date'],
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => 'La fecha seleccionada no está disponible para reservar.',
                'errors' => ['date' => [$exception->getMessage()]],
            ], 422);
        }

        if ($result === null) {
            return response()->json([
                'message' => 'El horario seleccionado no está disponible.',
                'code' => 'appointment_unavailable',
            ], 409);
        }

        return new PublicBookingAvailabilityResource($result);
    }
}
