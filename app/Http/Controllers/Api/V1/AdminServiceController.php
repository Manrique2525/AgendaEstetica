<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminServiceRequest;
use App\Http\Resources\AdminServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AdminServiceController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Service::query()->with('category:id,name,active')->withCount('appointments');

        if ($request->filled('q')) {
            $term = (string) $request->string('q');
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return AdminServiceResource::collection(
            $query->orderBy('service_category_id')->orderBy('name')->orderBy('id')->get(),
        );
    }

    public function store(AdminServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $image = $data['image'] ?? null;
        unset($data['image']);

        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), null);
        $data['active'] = $data['active'] ?? true;

        if (($data['pricing_type'] ?? null) === 'variable') {
            $data['price'] = null;
        }

        if ($image !== null) {
            $data['image_path'] = $image->store('services', 'public');
        }

        $service = Service::query()->create($data);

        return (new AdminServiceResource($this->load($service)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Service $service): AdminServiceResource
    {
        return new AdminServiceResource($this->load($service));
    }

    public function update(AdminServiceRequest $request, Service $service): AdminServiceResource
    {
        $data = $request->validated();
        $image = $data['image'] ?? null;
        unset($data['image']);

        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), $service->id);

        if (($data['pricing_type'] ?? null) === 'variable') {
            $data['price'] = null;
        }

        if (! array_key_exists('active', $data)) {
            unset($data['active']);
        }

        if ($image !== null) {
            $this->deleteImage($service);
            $data['image_path'] = $image->store('services', 'public');
        }

        $service->update($data);

        return new AdminServiceResource($this->load($service));
    }

    public function status(Request $request, Service $service): AdminServiceResource
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $service->update(['active' => $validated['active']]);

        return new AdminServiceResource($this->load($service));
    }

    public function destroy(Service $service): JsonResponse
    {
        if ($service->appointments()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un servicio con citas registradas. Desactívalo en su lugar.',
                'code' => 'service_has_appointments',
            ], 409);
        }

        $service->professionals()->detach();
        $this->deleteImage($service);
        $service->delete();

        return response()->json(status: 204);
    }

    private function deleteImage(Service $service): void
    {
        if ($service->image_path !== null) {
            Storage::disk('public')->delete($service->image_path);
        }
    }

    private function load(Service $service): Service
    {
        return $service->load('category:id,name,active')->loadCount('appointments');
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'servicio';
        $slug = $base;
        $suffix = 2;

        while (Service::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
