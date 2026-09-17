<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Professional;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CompleteAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment): Appointment {
            $profile = BusinessProfile::query()->where('singleton_key', 1)->lockForUpdate()->first();
            $snapshot = Appointment::query()->find($appointment->getKey());

            if ($profile === null || $snapshot === null) {
                throw (new ModelNotFoundException)->setModel(Appointment::class, [$appointment->getKey()]);
            }

            $professional = Professional::query()->whereKey($snapshot->professional_id)->lockForUpdate()->first();
            $lockedAppointment = Appointment::query()->whereKey($snapshot->id)->lockForUpdate()->first();

            if ($professional === null || $lockedAppointment === null) {
                throw new ModelNotFoundException;
            }
            if ($lockedAppointment->getRawOriginal('status') !== AppointmentStatus::CONFIRMED->value) {
                throw new InvalidArgumentException('Only confirmed appointments may be completed.');
            }

            $lockedAppointment->update(['status' => AppointmentStatus::COMPLETED]);
            $lockedAppointment->history()->create([
                'event_type' => AppointmentHistoryEventType::STATUS_CHANGED,
                'from_status' => AppointmentStatus::CONFIRMED,
                'to_status' => AppointmentStatus::COMPLETED,
            ]);

            return $lockedAppointment->fresh();
        });
    }
}
