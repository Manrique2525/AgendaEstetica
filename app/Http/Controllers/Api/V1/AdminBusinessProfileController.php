<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ReplaceBusinessHours;
use App\Http\Requests\AdminBusinessProfileRequest;
use App\Http\Resources\AdminBusinessProfileResource;
use App\Models\BusinessProfile;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class AdminBusinessProfileController
{
    /**
     * The business profile is a singleton: it is created by the seeder or the
     * first administrative update, never duplicated.
     */
    public function show(): AdminBusinessProfileResource
    {
        return new AdminBusinessProfileResource($this->profileOrFail()->load('hours'));
    }

    public function update(
        AdminBusinessProfileRequest $request,
        ReplaceBusinessHours $replaceBusinessHours,
    ): AdminBusinessProfileResource {
        $data = $request->validated();
        $hours = $data['hours'] ?? null;
        unset($data['hours']);

        $profile = BusinessProfile::query()->firstOrNew(['singleton_key' => 1]);
        $profile->fill($data);
        $profile->save();

        if ($hours !== null) {
            try {
                $replaceBusinessHours->execute($profile, $hours);
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'hours' => [$exception->getMessage()],
                ]);
            }
        }

        return new AdminBusinessProfileResource($profile->fresh()->load('hours'));
    }

    private function profileOrFail(): BusinessProfile
    {
        return BusinessProfile::query()->where('singleton_key', 1)->firstOrFail();
    }
}
