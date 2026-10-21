<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminProductCategoryRequest;
use App\Http\Resources\AdminProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class AdminProductCategoryController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ProductCategory::query()->withCount('products');

        if ($request->filled('q')) {
            $term = (string) $request->string('q');
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return AdminProductCategoryResource::collection(
            $query->orderBy('name')->orderBy('id')->get(),
        );
    }

    public function store(AdminProductCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), null);
        $data['active'] = $data['active'] ?? true;

        $category = ProductCategory::query()->create($data);

        return (new AdminProductCategoryResource($category->loadCount('products')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(AdminProductCategoryRequest $request, ProductCategory $productCategory): AdminProductCategoryResource
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), $productCategory->id);

        if (! array_key_exists('active', $data)) {
            unset($data['active']);
        }

        $productCategory->update($data);

        return new AdminProductCategoryResource($productCategory->loadCount('products'));
    }

    public function status(Request $request, ProductCategory $productCategory): AdminProductCategoryResource
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $productCategory->update(['active' => $validated['active']]);

        return new AdminProductCategoryResource($productCategory->loadCount('products'));
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        if ($productCategory->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con productos asociados.',
                'code' => 'product_category_has_products',
            ], 409);
        }

        $productCategory->delete();

        return response()->json(status: 204);
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'categoria-producto';
        $slug = $base;
        $suffix = 2;

        while (ProductCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
