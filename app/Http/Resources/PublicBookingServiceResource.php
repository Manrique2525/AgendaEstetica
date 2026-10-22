<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\ServicePricingType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class PublicBookingServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Service $service */
        $service = $this->resource;
        /** @var ServiceCategory|null $category */
        $category = $service->category;
        $pricingType = (string) $service->getRawOriginal('pricing_type');
        $amount = $service->price === null ? null : number_format((float) $service->price, 2, '.', ',');

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => $service->description,
            'image_url' => $service->image_path === null ? null : Storage::disk('public')->url($service->image_path),
            'duration_minutes' => $service->duration_minutes,
            'pricing_type' => $pricingType,
            'price' => $service->price,
            'pricing_display' => match ($pricingType) {
                ServicePricingType::FIXED->value => '$'.$amount,
                ServicePricingType::STARTING_FROM->value => 'Desde $'.$amount,
                ServicePricingType::VARIABLE->value => 'Precio variable',
                default => 'Precio no disponible',
            },
            'category' => $category === null ? null : [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
        ];
    }
}
