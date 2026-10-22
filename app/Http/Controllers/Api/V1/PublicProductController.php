<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PublicProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicProductController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return PublicProductResource::collection(Product::query()
            ->with('category:id,name')
            ->where('active', true)
            ->where(fn ($query) => $query
                ->whereNull('product_category_id')
                ->orWhereHas('category', fn ($category) => $category->where('active', true)))
            ->orderBy('product_category_id')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'product_category_id', 'name', 'slug', 'description', 'price']));
    }
}
