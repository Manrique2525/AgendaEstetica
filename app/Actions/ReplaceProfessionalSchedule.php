<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Professional;
use App\Support\ProfessionalScheduleValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class ReplaceProfessionalSchedule
{
    public function execute(Professional $professional, array $intervals): void
    {
        $validated = (new ProfessionalScheduleValidator)->validate($intervals);

        DB::transaction(function () use ($professional, $validated): void {
            $lockedProfessional = Professional::query()
                ->whereKey($professional->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedProfessional === null) {
                throw (new ModelNotFoundException)->setModel(Professional::class, [$professional->getKey()]);
            }

            $lockedProfessional->schedules()->delete();
            $lockedProfessional->schedules()->createMany($validated);
        });
    }
}
