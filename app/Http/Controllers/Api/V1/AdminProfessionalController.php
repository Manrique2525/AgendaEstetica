<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminProfessionalRequest;
use App\Http\Resources\AdminProfessionalResource;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

final class AdminProfessionalController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Professional::query()->with('services:id,name,active')->withCount('schedules');

        if ($request->filled('q')) {
            $term = (string) $request->string('q');
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('service_id')) {
            $query->whereHas('services', fn ($services) => $services->whereKey($request->integer('service_id')));
        }

        return AdminProfessionalResource::collection(
            $query->orderBy('name')->orderBy('id')->get(),
        );
    }

    public function store(AdminProfessionalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);
        $data['active'] = $data['active'] ?? true;

        $professional = DB::transaction(function () use ($data, $serviceIds): Professional {
            $professional = Professional::query()->create($data);

            if ($serviceIds !== []) {
                $professional->services()->sync($serviceIds);
            }

            return $professional;
        });

        return (new AdminProfessionalResource($this->load($professional)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Professional $professional): AdminProfessionalResource
    {
        return new AdminProfessionalResource($this->load($professional));
    }

    public function update(AdminProfessionalRequest $request, Professional $professional): AdminProfessionalResource
    {
        $data = $request->validated();
        $hasServices = array_key_exists('service_ids', $data);
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        if (! array_key_exists('active', $data)) {
            unset($data['active']);
        }

        DB::transaction(function () use ($professional, $data, $hasServices, $serviceIds): void {
            $professional->update($data);

            if ($hasServices) {
                $professional->services()->sync($serviceIds);
            }
        });

        return new AdminProfessionalResource($this->load($professional->fresh()));
    }

    public function status(Request $request, Professional $professional): AdminProfessionalResource
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $professional->update(['active' => $validated['active']]);

        return new AdminProfessionalResource($this->load($professional->fresh()));
    }

    public function destroy(Professional $professional): JsonResponse
    {
        if ($professional->appointments()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un profesional con citas registradas. Desactívalo en su lugar.',
                'code' => 'professional_has_appointments',
            ], 409);
        }

        $professional->services()->detach();
        $professional->delete();

        return response()->json(status: 204);
    }

    private function load(Professional $professional): Professional
    {
        return $professional->load('services:id,name,active')->loadCount(['schedules', 'appointments']);
    }
}
