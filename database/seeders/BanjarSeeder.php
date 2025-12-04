<?php

namespace Database\Seeders;

use App\Models\Banjar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BanjarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banjars = [
            [
                'name' => 'Banjar Dauh Peken',
                'address' => 'Jalan Raya Denpasar, Ubud, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Kaja',
                'address' => 'Jalan Monkey Forest, Ubud, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Kelod',
                'address' => 'Jalan Raya Ubud, Ubud, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Kangin',
                'address' => 'Jalan Raya Campuhan, Ubud, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Kauh',
                'address' => 'Jalan Raya Sanggingan, Ubud, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Tegallalang',
                'address' => 'Jalan Raya Tegallalang, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Payangan',
                'address' => 'Jalan Raya Payangan, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Sukawati',
                'address' => 'Jalan Raya Sukawati, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Blahbatuh',
                'address' => 'Jalan Raya Blahbatuh, Gianyar, Bali',
            ],
            [
                'name' => 'Banjar Tampaksiring',
                'address' => 'Jalan Raya Tampaksiring, Gianyar, Bali',
            ],
        ];

        foreach ($banjars as $banjar) {
            Banjar::create($banjar);
        }
    }
}
