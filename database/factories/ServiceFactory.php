<?php

namespace Database\Factories;

use App\Enums\ServicePricingType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => 'Technical test service description.',
            'duration_minutes' => 60,
            'pricing_type' => ServicePricingType::FIXED,
            'price' => '100.00',
            'active' => true,
        ];
    }
}
