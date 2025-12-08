<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 10000, 100000),
            'date' => $this->faker->date(),
            'method' => $this->faker->randomElement(['cash', 'transfer', 'qris']),
            'user_id' => User::factory(), // Admin who validated it
            'status' => $this->faker->randomElement(['paid', 'pending', 'invalid']),
        ];
    }
}
