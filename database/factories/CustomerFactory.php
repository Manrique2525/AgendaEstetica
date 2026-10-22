<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $phone = '+52993'.fake()->numerify('#######');

        return [
            'name' => fake()->name(),
            'phone' => $phone,
            'phone_normalized' => $phone,
        ];
    }
}
