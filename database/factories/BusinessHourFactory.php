<?php

namespace Database\Factories;

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessHour>
 */
class BusinessHourFactory extends Factory
{
    protected $model = BusinessHour::class;

    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'weekday' => 1,
            'interval_order' => 1,
            'opens_at' => '10:00',
            'closes_at' => '20:00',
        ];
    }
}
