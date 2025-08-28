<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'nama' => 'Dr. Ahmad Suryadi',
                'nip' => '196801011990031001',
                'tanggal_lahir' => '1968-01-01',
                'pangkat_golongan' => 'III/d',
                'jabatan' => 'Kepala Puskesmas',
            ],
            [
                'nama' => 'Ns. Siti Nurhaliza, S.Kep',
                'nip' => '197505151998032001',
                'tanggal_lahir' => '1975-05-15',
                'pangkat_golongan' => 'III/c',
                'jabatan' => 'Perawat Koordinator',
            ],
            [
                'nama' => 'Budi Santoso, SKM',
                'nip' => '198203102005011002',
                'tanggal_lahir' => '1982-03-10',
                'pangkat_golongan' => 'III/b',
                'jabatan' => 'Sanitarian',
            ],
            [
                'nama' => 'Rina Marlina, Amd.Keb',
                'nip' => '198907252010012003',
                'tanggal_lahir' => '1989-07-25',
                'pangkat_golongan' => 'II/d',
                'jabatan' => 'Bidan',
            ],
            [
                'nama' => 'Joko Widodo, S.Gz',
                'nip' => '199101152015031004',
                'tanggal_lahir' => '1991-01-15',
                'pangkat_golongan' => 'II/c',
                'jabatan' => 'Ahli Gizi',
            ],
        ];

        foreach ($employees as $employee) {
            \App\Models\Employee::create($employee);
        }
    }
}
