<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;
        /** @var ProductCategory|null $category */
        $category = $product->category;
        $amount = number_format((float) $product->price, 2, '.', ',');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'price_display' => '$'.$amount,
            'category' => $category === null ? null : [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ];
    }
}
