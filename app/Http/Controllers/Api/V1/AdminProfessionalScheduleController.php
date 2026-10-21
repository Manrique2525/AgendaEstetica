<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ReplaceProfessionalSchedule;
use App\Http\Requests\AdminProfessionalScheduleRequest;
use App\Http\Resources\AdminProfessionalScheduleResource;
use App\Models\Professional;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class AdminProfessionalScheduleController
{
    public function index(Professional $professional): AnonymousResourceCollection
    {
        return AdminProfessionalScheduleResource::collection(
            $professional->schedules()->orderBy('weekday')->orderBy('starts_at')->get(),
        );
    }

    public function update(
        AdminProfessionalScheduleRequest $request,
        Professional $professional,
        ReplaceProfessionalSchedule $replaceProfessionalSchedule,
    ): AnonymousResourceCollection {
        try {
            $replaceProfessionalSchedule->execute($professional, $request->validated('intervals'));
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'intervals' => [$exception->getMessage()],
            ]);
        }

        return AdminProfessionalScheduleResource::collection(
            $professional->fresh()->schedules()->orderBy('weekday')->orderBy('starts_at')->get(),
        );
    }
}
