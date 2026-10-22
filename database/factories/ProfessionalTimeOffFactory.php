<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\ProfessionalTimeOff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfessionalTimeOff>
 */
class ProfessionalTimeOffFactory extends Factory
{
    protected $model = ProfessionalTimeOff::class;

    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'starts_at' => '2026-01-15 10:00:00',
            'ends_at' => '2026-01-15 12:00:00',
        ];
    }
}
