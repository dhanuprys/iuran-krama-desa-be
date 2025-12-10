<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['create', 'update', 'delete', 'login']),
            'model_type' => $this->faker->randomElement(['App\Models\User', 'App\Models\Resident']),
            'model_id' => $this->faker->randomNumber(),
            'old_values' => ['field' => 'old_value'],
            'new_values' => ['field' => 'new_value'],
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
        ];
    }
}
