<?php

namespace Database\Factories;

use App\Enums\AppointmentHistoryEventType;
use App\Models\Appointment;
use App\Models\AppointmentHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentHistory>
 */
class AppointmentHistoryFactory extends Factory
{
    protected $model = AppointmentHistory::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'event_type' => AppointmentHistoryEventType::CREATED,
            'from_status' => null,
            'to_status' => null,
            'old_starts_at' => null,
            'old_ends_at' => null,
            'new_starts_at' => null,
            'new_ends_at' => null,
            'old_professional_id' => null,
            'new_professional_id' => null,
        ];
    }
}
