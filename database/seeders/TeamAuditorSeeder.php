<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamAuditorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = \Illuminate\Support\Facades\Hash::make('password');

        $tim1 = [
            ['name' => 'Beti Feberiane', 'role' => 'auditor', 'nip' => '198302232011012002', 'jabatan' => 'Auditor Ahli Muda', 'tim' => 'tim_1'],
            ['name' => 'Sigit Hero Christanto', 'role' => 'ketua_tim', 'nip' => '198005182011011003', 'jabatan' => 'PPUPD Ahli Muda', 'tim' => 'tim_1'],
            ['name' => 'Niken Pravitasari', 'role' => 'auditor', 'nip' => '199107132025062001', 'jabatan' => 'PPUPD Ahli Pertama', 'tim' => 'tim_1'],
            ['name' => 'Ayu Monica Arafat', 'role' => 'auditor', 'nip' => '199210112025062001', 'jabatan' => 'PPUPD Ahli Pertama', 'tim' => 'tim_1'],
            ['name' => 'Bernardus Suhartawan', 'role' => 'auditor', 'nip' => '199802062025061001', 'jabatan' => 'Auditor Ahli Pertama', 'tim' => 'tim_1'],
            ['name' => 'Claudea Anastasia', 'role' => 'auditor', 'nip' => '199604022025062004', 'jabatan' => 'Auditor Terampil', 'tim' => 'tim_1'],
            ['name' => 'St. Noraida Rahmiati', 'role' => 'auditor', 'nip' => '199611212025062002', 'jabatan' => 'Auditor Terampil', 'tim' => 'tim_1'],
            ['name' => 'Mella Shinta Kumalasari', 'role' => 'auditor', 'nip' => '199809142025062002', 'jabatan' => 'Auditor Terampil', 'tim' => 'tim_1'],
        ];

        foreach ($tim1 as $user) {
            $email = strtolower(explode(' ', $user['name'])[0] . '.' . (explode(' ', $user['name'])[1] ?? 'user') . '@audit.local');
            \App\Models\User::updateOrCreate(
                ['email' => $email],
                array_merge($user, ['password' => $password])
            );
        }

        $tim2 = [
            ['name' => 'Maswan', 'role' => 'ketua_tim', 'nip' => '197501012005011001', 'jabatan' => 'Auditor Madya', 'tim' => 'tim_2'],
            ['name' => 'Dummy Satu', 'role' => 'auditor', 'nip' => '123456789012345001', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Dua', 'role' => 'auditor', 'nip' => '123456789012345002', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Tiga', 'role' => 'auditor', 'nip' => '123456789012345003', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Empat', 'role' => 'auditor', 'nip' => '123456789012345004', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Lima', 'role' => 'auditor', 'nip' => '123456789012345005', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Enam', 'role' => 'auditor', 'nip' => '123456789012345006', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
            ['name' => 'Dummy Tujuh', 'role' => 'auditor', 'nip' => '123456789012345007', 'jabatan' => 'Auditor Pertama', 'tim' => 'tim_2'],
        ];

        foreach ($tim2 as $user) {
            $email = strtolower(explode(' ', $user['name'])[0] . '.' . (explode(' ', $user['name'])[1] ?? 'user') . '@audit.local');
            \App\Models\User::updateOrCreate(
                ['email' => $email],
                array_merge($user, ['password' => $password])
            );
        }
    }
}
