<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Shift;
use App\Models\ShiftRequirement;
use App\Models\Staff;
use App\Models\User;
use App\Models\WorkLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun DEMO lokal saja. Ganti password / hapus data contoh sebelum implementasi riil.
        $admin = User::updateOrCreate(['email' => 'admin@example.test'], [
            'name' => 'Admin Operasional (Demo)',
            'role' => 'admin',
            'password' => Hash::make('DemoAdmin123!'),
        ]);
        $names = [
            'Andi Saputra',
            'Budi Santoso',
            'Candra Wijaya',
            'Deni Kurniawan',
            'Eka Pratama',
            'Fajar Hidayat',
            'Gilang Ramadhan',
            'Hendra Gunawan',
            'Indra Permana',
            'Joko Susilo',
            'Kevin Mahendra',
            'Lukman Hakim'
        ];
        foreach ($names as $index => $name) {
            $person = Staff::updateOrCreate(['code' => sprintf('STF-%03d', $index + 1)], [
                'name' => $name,
                'skill' => 'Cleaning Service',
                'is_active' => true,
            ]);
            Availability::updateOrCreate(['staff_id' => $person->id, 'date' => today()->toDateString()], [
                'status' => $index === 3 ? 'leave' : ($index === 8 ? 'unavailable' : 'available'),
                'notes' => 'Data dummy untuk pengujian',
                'updated_by' => $admin->id,
            ]);
            if ($index === 0) {
                User::updateOrCreate(['email' => 'staff@example.test'], [
                    'name' => $name,
                    'password' => Hash::make('DemoStaff123!'),
                    'role' => 'staff',
                    'staff_id' => $person->id,
                ]);
            }
        }
        $locations = [
            WorkLocation::updateOrCreate(['name' => 'Gedung Perkantoran A (Contoh)'], ['address' => 'Lokasi dummy 1']),
            WorkLocation::updateOrCreate(['name' => 'Area Komersial B (Contoh)'], ['address' => 'Lokasi dummy 2']),
        ];
        $shifts = [
            Shift::updateOrCreate(['name' => 'Pagi'], ['start_time' => '07:00:00', 'end_time' => '15:00:00']),
            Shift::updateOrCreate(['name' => 'Siang'], ['start_time' => '15:00:00', 'end_time' => '23:00:00']),
            Shift::updateOrCreate(['name' => 'Malam'], ['start_time' => '23:00:00', 'end_time' => '07:00:00']),
        ];
        foreach ($locations as $i => $location) {
            foreach ($shifts as $j => $shift) {
                ShiftRequirement::updateOrCreate(['location_id' => $location->id, 'shift_id' => $shift->id], [
                    'required_count' => $i === 0 ? ($j === 0 ? 3 : 2) : ($j === 0 ? 2 : 1),
                ]);
            }
        }
    }
}
