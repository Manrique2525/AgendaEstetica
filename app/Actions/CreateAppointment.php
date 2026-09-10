<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateAppointment
{
    public function execute(
        Customer $customer,
        Service $service,
        Professional $professional,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): Appointment {
        return DB::transaction(function () use ($customer, $service, $professional, $startsAt, $endsAt): Appointment {
            $profile = BusinessProfile::query()
                ->where('singleton_key', 1)
                ->lockForUpdate()
                ->first();

            if ($profile === null) {
                throw new InvalidArgumentException('Business profile is not configured.');
            }

            $lockedProfessional = Professional::query()
                ->whereKey($professional->getKey())
                ->lockForUpdate()
                ->first();
            $freshCustomer = Customer::query()->find($customer->getKey());
            $freshService = Service::query()->lockForUpdate()->find($service->getKey());

            if ($lockedProfessional === null || $freshCustomer === null || $freshService === null) {
                throw (new ModelNotFoundException)->setModel(Appointment::class);
            }

            if (! (new CheckAppointmentAvailability)->execute(
                $freshService,
                $lockedProfessional,
                $startsAt,
                $endsAt,
                null,
                null,
                true,
            )) {
                throw new InvalidArgumentException('Appointment is unavailable.');
            }

            $appointment = Appointment::query()->create([
                'customer_id' => $freshCustomer->id,
                'service_id' => $freshService->id,
                'professional_id' => $lockedProfessional->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_minutes' => $freshService->duration_minutes,
                'status' => AppointmentStatus::CONFIRMED,
            ]);

            $appointment->history()->create([
                'event_type' => AppointmentHistoryEventType::CREATED,
                'to_status' => AppointmentStatus::CONFIRMED,
                'new_starts_at' => $appointment->starts_at,
                'new_ends_at' => $appointment->ends_at,
                'new_professional_id' => $appointment->professional_id,
            ]);

            return $appointment->fresh();
        });
    }
}
