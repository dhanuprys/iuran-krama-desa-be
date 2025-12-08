<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $iuran = $this->faker->randomFloat(2, 10000, 50000);
        $peturunan = $this->faker->randomFloat(2, 5000, 20000);
        $dedosan = $this->faker->randomFloat(2, 0, 10000);
        $total = $iuran + $peturunan + $dedosan;

        return [
            'resident_id' => Resident::factory(),
            'invoice_date' => $this->faker->date(),
            'iuran_amount' => $iuran,
            'peturunan_amount' => $peturunan,
            'dedosan_amount' => $dedosan,
            'total_amount' => $total,
            'user_id' => User::factory(), // Admin who created it
        ];
    }
}
