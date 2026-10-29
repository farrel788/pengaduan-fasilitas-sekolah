<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\FacilityCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sekolah.test'],
            [
                'name' => 'Admin Sarpras',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'teknisi@sekolah.test'],
            [
                'name' => 'Pak Teknisi',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
            ]
        );

        User::updateOrCreate(
            ['nisn' => '1234567890'],
            [
                'name' => 'Budi Siswa',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]
        );

        User::updateOrCreate(
            ['nisn' => '0987654321'],
            [
                'name' => 'Sari Siswa',
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]
        );

        $areas = ['Kelas', 'Laboratorium', 'Lapangan', 'Toilet', 'Ruang MICE', 'Aula'];
        foreach ($areas as $namaArea) {
            Area::firstOrCreate(['name' => $namaArea]);
        }

        $areaLocations = [
            'Kelas' => ['XII.PPLG.1', 'XII.PPLG.2', 'XI.PPLG.1'],
            'Laboratorium' => ['Lab Komputer 1', 'Lab Komputer 2', 'Lab IPA'],
            'Lapangan' => ['Lapangan Utama', 'Lapangan Basket'],
            'Toilet' => ['Toilet Lantai 1', 'Toilet Lantai 2'],
            'Ruang MICE' => ['Ruang MICE 1', 'Ruang MICE 2'],
            'Aula' => ['Aula Utama'],
        ];

        foreach ($areaLocations as $areaName => $locations) {
            $area = Area::where('name', $areaName)->first();
            foreach ($locations as $loc) {
                $area->locations()->firstOrCreate(['name' => $loc]);
            }
        }

        foreach (['Meja/Kursi', 'AC/Kipas', 'Proyektor', 'Papan Tulis', 'Lampu', 'Komputer', 'Toilet'] as $nama) {
            FacilityCategory::firstOrCreate(['name' => $nama]);
        }
    }
}
