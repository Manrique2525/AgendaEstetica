<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProfile>
 */
class BusinessProfileFactory extends Factory
{
    protected $model = BusinessProfile::class;

    public function definition(): array
    {
        return [
            'singleton_key' => 1,
            'name' => 'Technical Business',
            'phone' => '+529931234567',
            'timezone' => 'UTC',
            'max_simultaneous_clients' => 8,
        ];
    }
}
