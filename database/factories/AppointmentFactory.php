<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'service_id' => Service::factory(),
            'professional_id' => Professional::factory(),
            'starts_at' => '2026-01-15 16:00:00',
            'ends_at' => '2026-01-15 17:00:00',
            'duration_minutes' => 60,
            'status' => AppointmentStatus::CONFIRMED,
        ];
    }
}
