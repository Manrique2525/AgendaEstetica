<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AdminAgendaContextResource;
use App\Models\BusinessProfile;

final class AdminAgendaContextController
{
    public function __invoke(): AdminAgendaContextResource
    {
        $profile = BusinessProfile::query()
            ->where('singleton_key', 1)
            ->firstOrFail();

        return new AdminAgendaContextResource($profile);
    }
}
