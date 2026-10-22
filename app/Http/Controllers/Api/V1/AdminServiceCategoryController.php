<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminServiceCategoryRequest;
use App\Http\Resources\AdminServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class AdminServiceCategoryController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ServiceCategory::query()->withCount('services');

        if ($request->filled('q')) {
            $term = (string) $request->string('q');
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return AdminServiceCategoryResource::collection(
            $query->orderBy('name')->orderBy('id')->get(),
        );
    }

    public function store(AdminServiceCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), null);
        $data['active'] = $data['active'] ?? true;

        $category = ServiceCategory::query()->create($data);

        return (new AdminServiceCategoryResource($category->loadCount('services')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ServiceCategory $serviceCategory): AdminServiceCategoryResource
    {
        return new AdminServiceCategoryResource($serviceCategory->loadCount('services'));
    }

    public function update(AdminServiceCategoryRequest $request, ServiceCategory $serviceCategory): AdminServiceCategoryResource
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), $serviceCategory->id);

        if (! array_key_exists('active', $data)) {
            unset($data['active']);
        }

        $serviceCategory->update($data);

        return new AdminServiceCategoryResource($serviceCategory->loadCount('services'));
    }

    public function status(Request $request, ServiceCategory $serviceCategory): AdminServiceCategoryResource
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $serviceCategory->update(['active' => $validated['active']]);

        return new AdminServiceCategoryResource($serviceCategory->loadCount('services'));
    }

    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        if ($serviceCategory->services()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con servicios asociados.',
                'code' => 'category_has_services',
            ], 409);
        }

        $serviceCategory->delete();

        return response()->json(status: 204);
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'categoria';
        $slug = $base;
        $suffix = 2;

        while (ServiceCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
