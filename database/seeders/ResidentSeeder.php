<?php

namespace Database\Seeders;

use App\Models\Resident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residents = [
            [
                'nik' => '5103021234567890',
                'name' => 'I Made Surya',
                'gender' => 'L',
                'resident_status_id' => 1, // Krama
                'banjar_id' => 1, // Banjar Dauh Peken
                'address' => 'Jalan Raya Denpasar No. 15, Ubud, Gianyar',
                'phone' => '081234567890',
            ],
            [
                'nik' => '5103021234567891',
                'name' => 'Ni Ketut Sari',
                'gender' => 'P',
                'resident_status_id' => 2, // Krama Tamu
                'banjar_id' => 1, // Banjar Dauh Peken
                'address' => 'Jalan Raya Denpasar No. 23, Ubud, Gianyar',
                'phone' => '081234567891',
            ],
            [
                'nik' => '5103021234567892',
                'name' => 'I Wayan Gede',
                'gender' => 'L',
                'resident_status_id' => 3, // Tamu
                'banjar_id' => 2, // Banjar Kaja
                'address' => 'Jalan Monkey Forest No. 8, Ubud, Gianyar',
                'phone' => '081234567892',
            ],
            [
                'nik' => '5103021234567893',
                'name' => 'Ni Made Ayu',
                'gender' => 'P',
                'resident_status_id' => 1, // Krama
                'banjar_id' => 2, // Banjar Kaja
                'address' => 'Jalan Monkey Forest No. 12, Ubud, Gianyar',
                'phone' => '081234567893',
            ],
            [
                'nik' => '5103021234567894',
                'name' => 'I Putu Adi',
                'gender' => 'L',
                'resident_status_id' => 2, // Krama Tamu
                'banjar_id' => 3, // Banjar Kelod
                'address' => 'Jalan Raya Ubud No. 45, Ubud, Gianyar',
                'phone' => '081234567894',
            ],
            [
                'nik' => '5103021234567895',
                'name' => 'Ni Luh Putu',
                'gender' => 'P',
                'resident_status_id' => 3, // Tamu
                'banjar_id' => 3, // Banjar Kelod
                'address' => 'Jalan Raya Ubud No. 67, Ubud, Gianyar',
                'phone' => '081234567895',
            ],
            [
                'nik' => '5103021234567896',
                'name' => 'I Komang Rai',
                'gender' => 'L',
                'resident_status_id' => 1, // Krama
                'banjar_id' => 4, // Banjar Kangin
                'address' => 'Jalan Raya Campuhan No. 34, Ubud, Gianyar',
                'phone' => '081234567896',
            ],
            [
                'nik' => '5103021234567897',
                'name' => 'Ni Made Sari',
                'gender' => 'P',
                'resident_status_id' => 2, // Krama Tamu
                'banjar_id' => 4, // Banjar Kangin
                'address' => 'Jalan Raya Campuhan No. 56, Ubud, Gianyar',
                'phone' => '081234567897',
            ],
            [
                'nik' => '5103021234567898',
                'name' => 'I Gede Agus',
                'gender' => 'L',
                'resident_status_id' => 3, // Tamu
                'banjar_id' => 5, // Banjar Kauh
                'address' => 'Jalan Raya Sanggingan No. 78, Ubud, Gianyar',
                'phone' => '081234567898',
            ],
            [
                'nik' => '5103021234567899',
                'name' => 'Ni Ketut Dewi',
                'gender' => 'P',
                'resident_status_id' => 1, // Krama
                'banjar_id' => 5, // Banjar Kauh
                'address' => 'Jalan Raya Sanggingan No. 90, Ubud, Gianyar',
                'phone' => '081234567899',
            ],
            [
                'nik' => '5103021234567900',
                'name' => 'I Made Jaya',
                'gender' => 'L',
                'resident_status_id' => 2, // Krama Tamu
                'banjar_id' => 6, // Banjar Tegallalang
                'address' => 'Jalan Raya Tegallalang No. 12, Gianyar',
                'phone' => '081234567900',
            ],
            [
                'nik' => '5103021234567901',
                'name' => 'Ni Luh Made',
                'gender' => 'P',
                'resident_status_id' => 3, // Tamu
                'banjar_id' => 6, // Banjar Tegallalang
                'address' => 'Jalan Raya Tegallalang No. 34, Gianyar',
                'phone' => '081234567901',
            ],
            [
                'nik' => '5103021234567902',
                'name' => 'I Wayan Putra',
                'gender' => 'L',
                'resident_status_id' => 1, // Krama
                'banjar_id' => 7, // Banjar Payangan
                'address' => 'Jalan Raya Payangan No. 56, Gianyar',
                'phone' => '081234567902',
            ],
            [
                'nik' => '5103021234567903',
                'name' => 'Ni Made Sari',
                'gender' => 'P',
                'resident_status_id' => 2, // Krama Tamu
                'banjar_id' => 7, // Banjar Payangan
                'address' => 'Jalan Raya Payangan No. 78, Gianyar',
                'phone' => '081234567903',
            ],
            [
                'nik' => '5103021234567904',
                'name' => 'I Komang Surya',
                'gender' => 'L',
                'resident_status_id' => 3, // Tamu
                'banjar_id' => 8, // Banjar Sukawati
                'address' => 'Jalan Raya Sukawati No. 90, Gianyar',
                'phone' => '081234567904',
            ],
        ];

        foreach ($residents as $resident) {
            Resident::create($resident);
        }
    }
}
