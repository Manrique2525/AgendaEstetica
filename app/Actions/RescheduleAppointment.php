<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RescheduleAppointment
{
    public function execute(
        Appointment $appointment,
        Professional $newProfessional,
        CarbonImmutable $newStartsAt,
        CarbonImmutable $newEndsAt,
    ): Appointment {
        return DB::transaction(function () use ($appointment, $newProfessional, $newStartsAt, $newEndsAt): Appointment {
            $profile = BusinessProfile::query()
                ->where('singleton_key', 1)
                ->lockForUpdate()
                ->first();
            $snapshot = Appointment::query()->find($appointment->getKey());

            if ($profile === null || $snapshot === null) {
                throw (new ModelNotFoundException)->setModel(Appointment::class, [$appointment->getKey()]);
            }

            $professionalIds = collect([$snapshot->professional_id, $newProfessional->getKey()])
                ->unique()
                ->sort()
                ->values();
            $lockedProfessionals = [];

            foreach ($professionalIds as $professionalId) {
                $lockedProfessionals[$professionalId] = Professional::query()
                    ->whereKey($professionalId)
                    ->lockForUpdate()
                    ->first();

                if ($lockedProfessionals[$professionalId] === null) {
                    throw new ModelNotFoundException;
                }
            }

            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedAppointment === null) {
                throw (new ModelNotFoundException)->setModel(Appointment::class, [$appointment->getKey()]);
            }

            if ($lockedAppointment->getRawOriginal('status') !== AppointmentStatus::CONFIRMED->value) {
                throw new InvalidArgumentException('Only confirmed appointments may be rescheduled.');
            }

            if ($lockedAppointment->professional_id === $newProfessional->getKey()
                && CarbonImmutable::parse((string) $lockedAppointment->starts_at, 'UTC')->equalTo($newStartsAt)
                && CarbonImmutable::parse((string) $lockedAppointment->ends_at, 'UTC')->equalTo($newEndsAt)) {
                return $lockedAppointment->fresh();
            }

            $service = Service::query()->lockForUpdate()->find($lockedAppointment->service_id);

            if ($service === null) {
                throw new ModelNotFoundException;
            }

            $targetProfessional = $lockedProfessionals[$newProfessional->getKey()];

            if (! (new CheckAppointmentAvailability)->execute(
                $service,
                $targetProfessional,
                $newStartsAt,
                $newEndsAt,
                $lockedAppointment->id,
                $lockedAppointment->duration_minutes,
                true,
            )) {
                throw new InvalidArgumentException('Appointment is unavailable.');
            }

            $oldStartsAt = $lockedAppointment->starts_at;
            $oldEndsAt = $lockedAppointment->ends_at;
            $oldProfessionalId = $lockedAppointment->professional_id;

            $lockedAppointment->update([
                'professional_id' => $targetProfessional->id,
                'starts_at' => $newStartsAt,
                'ends_at' => $newEndsAt,
            ]);

            $lockedAppointment->history()->create([
                'event_type' => AppointmentHistoryEventType::RESCHEDULED,
                'from_status' => AppointmentStatus::CONFIRMED,
                'to_status' => AppointmentStatus::CONFIRMED,
                'old_starts_at' => $oldStartsAt,
                'old_ends_at' => $oldEndsAt,
                'new_starts_at' => $newStartsAt,
                'new_ends_at' => $newEndsAt,
                'old_professional_id' => $oldProfessionalId,
                'new_professional_id' => $targetProfessional->id,
            ]);

            return $lockedAppointment->fresh();
        });
    }
}
