<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            echo "Creating Admin User...\n";
            // 1. Create Admin User
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);

            echo "Creating Krama User...\n";
            // 2. Create Krama User
            $krama = User::factory()->create([
                'name' => 'Krama User',
                'email' => 'krama@example.com',
                'password' => bcrypt('password'),
                'role' => 'krama',
            ]);

            // 3. Seed Master Data (Banjars & Resident Statuses)
            // We use the existing seeders for consistent real-world data
            $this->call([
                BanjarSeeder::class,
                ResidentStatusSeeder::class,
            ]);

            // 4. Create Residents (Families) for Krama User
            $banjars = \App\Models\Banjar::all();
            $statuses = \App\Models\ResidentStatus::all();

            // Family 1: Complete Family
            $kk1 = '1111111111111111';
            // Head
            $head1 = \App\Models\Resident::factory()->create([
                'user_id' => $krama->id,
                'created_by_user_id' => $krama->id,
                'banjar_id' => $banjars->random()->id,
                'resident_status_id' => $statuses->random()->id,
                'family_card_number' => $kk1,
                'family_status' => 'HEAD_OF_FAMILY',
                'name' => 'Bapak Budi',
                'gender' => 'L',
                'validation_status' => 'APPROVED',
            ]);
            // Wife
            \App\Models\Resident::factory()->create([
                'user_id' => $krama->id,
                'created_by_user_id' => $krama->id,
                'banjar_id' => $head1->banjar_id,
                'resident_status_id' => $head1->resident_status_id,
                'family_card_number' => $kk1,
                'family_status' => 'WIFE',
                'name' => 'Ibu Budi',
                'gender' => 'P',
                'validation_status' => 'APPROVED',
            ]);
            // Child
            \App\Models\Resident::factory()->create([
                'user_id' => $krama->id,
                'created_by_user_id' => $krama->id,
                'banjar_id' => $head1->banjar_id,
                'resident_status_id' => $head1->resident_status_id,
                'family_card_number' => $kk1,
                'family_status' => 'CHILD',
                'name' => 'Anak Budi',
                'gender' => 'L',
                'validation_status' => 'APPROVED',
            ]);

            // Family 2: Single Head
            $kk2 = '2222222222222222';
            $head2 = \App\Models\Resident::factory()->create([
                'user_id' => $krama->id,
                'created_by_user_id' => $krama->id,
                'banjar_id' => $banjars->random()->id,
                'resident_status_id' => $statuses->random()->id,
                'family_card_number' => $kk2,
                'family_status' => 'HEAD_OF_FAMILY',
                'name' => 'Bapak Single',
                'gender' => 'L',
                'validation_status' => 'APPROVED',
            ]);

            // 5. Create Invoices
            \App\Models\Invoice::factory()->count(3)->create([
                'resident_id' => $head1->id,
                'user_id' => $admin->id,
            ]);
            \App\Models\Invoice::factory()->count(2)->create([
                'resident_id' => $head2->id,
                'user_id' => $admin->id,
            ]);

            // 6. Create Announcements
            \App\Models\Announcement::factory()->count(5)->create([
                'created_by' => $admin->id,
            ]);

            // 7. Create Pending Resident Application
            \App\Models\Resident::factory()->create([
                'user_id' => $krama->id,
                'created_by_user_id' => $krama->id,
                'banjar_id' => $banjars->random()->id,
                'resident_status_id' => $statuses->random()->id,
                'family_card_number' => '3333333333333333',
                'validation_status' => 'PENDING',
                'name' => 'Pending Resident Application',
                'family_status' => 'HEAD_OF_FAMILY',
            ]);
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
