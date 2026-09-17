<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaProfessionalLookupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Professional $professional */
        $professional = $this->resource;

        return [
            'id' => $professional->id,
            'name' => $professional->name,
        ];
    }
}
