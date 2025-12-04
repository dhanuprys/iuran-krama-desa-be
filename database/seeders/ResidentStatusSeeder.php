<?php

namespace Database\Seeders;

use App\Models\ResidentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResidentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residentStatuses = [
            [
                'name' => 'Krama',
                'contribution_amount' => 50000.00,
            ],
            [
                'name' => 'Krama Tamu',
                'contribution_amount' => 30000.00,
            ],
            [
                'name' => 'Tamu',
                'contribution_amount' => 20000.00,
            ],
        ];

        foreach ($residentStatuses as $status) {
            ResidentStatus::create($status);
        }
    }
}
