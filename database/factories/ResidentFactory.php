<?php

namespace Database\Factories;

use App\Models\Banjar;
use App\Models\ResidentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'family_card_number' => $this->faker->numerify('################'),
            'name' => $this->faker->name,
            'gender' => $this->faker->randomElement(['L', 'P']),
            'place_of_birth' => $this->faker->city,
            'date_of_birth' => $this->faker->date(),
            'family_status' => $this->faker->randomElement(['HEAD_OF_FAMILY', 'PARENT', 'HUSBAND', 'WIFE', 'CHILD']),
            'religion' => 'Hindu',
            'education' => $this->faker->randomElement(['SD', 'SMP', 'SMA', 'S1', 'S2']),
            'work_type' => $this->faker->jobTitle,
            'marital_status' => $this->faker->randomElement(['MARRIED', 'SINGLE', 'DEAD_DIVORCE', 'LIVING_DIVORCE']),
            'origin_address' => $this->faker->address,
            'residential_address' => $this->faker->address,
            'house_number' => $this->faker->buildingNumber,
            'arrival_date' => $this->faker->date(),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->safeEmail,
            'validation_status' => 'APPROVED',
            'village_status' => $this->faker->randomElement(['NEGAK', 'PEMIRAK', 'PENGAMPEL']),
            'user_id' => User::factory(),
            'created_by_user_id' => User::factory(),
            'banjar_id' => Banjar::factory(),
            'resident_status_id' => ResidentStatus::factory(),
        ];
    }
}
