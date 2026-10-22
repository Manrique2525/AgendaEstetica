<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AdminProductRequest;
use App\Http\Resources\AdminProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class AdminProductController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()->with('category:id,name,active');

        if ($request->filled('q')) {
            $term = (string) $request->string('q');
            $query->where('name', 'like', "%{$term}%");
        }

        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->integer('product_category_id'));
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return AdminProductResource::collection(
            $query->orderBy('name')->orderBy('id')->get(),
        );
    }

    public function store(AdminProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), null);
        $data['active'] = $data['active'] ?? true;

        $product = Product::query()->create($data);

        return (new AdminProductResource($product->load('category:id,name,active')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): AdminProductResource
    {
        return new AdminProductResource($product->load('category:id,name,active'));
    }

    public function update(AdminProductRequest $request, Product $product): AdminProductResource
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']), $product->id);

        if (! array_key_exists('active', $data)) {
            unset($data['active']);
        }

        $product->update($data);

        return new AdminProductResource($product->load('category:id,name,active'));
    }

    public function status(Request $request, Product $product): AdminProductResource
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);

        $product->update(['active' => $validated['active']]);

        return new AdminProductResource($product->load('category:id,name,active'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(status: 204);
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'producto';
        $slug = $base;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
