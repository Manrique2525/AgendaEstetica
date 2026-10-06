<?php

namespace Database\Factories;

use App\Enums\ServicePricingType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => 'Technical test service description.',
            'duration_minutes' => 60,
            'pricing_type' => ServicePricingType::FIXED,
            'price' => '100.00',
            'active' => true,
        ];
    }
}
