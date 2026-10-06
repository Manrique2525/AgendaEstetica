<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAgendaContextResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BusinessProfile $profile */
        $profile = $this->resource;

        return ['timezone' => $profile->timezone];
    }
}
