<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfessionalSchedule>
 */
class ProfessionalScheduleFactory extends Factory
{
    protected $model = ProfessionalSchedule::class;

    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'weekday' => 1,
            'starts_at' => '09:00',
            'ends_at' => '17:00',
        ];
    }
}
