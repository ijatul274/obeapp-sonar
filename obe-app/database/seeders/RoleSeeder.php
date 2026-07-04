<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Administrator',
            'identity' => 'admin',
            'email' => 'admin@unri.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Kaprodi
        User::create([
            'name' => 'Kepala Prodi',
            'identity' => 'kaprodi',
            'email' => 'kaprodi@unri.ac.id',
            'password' => Hash::make('password'),
            'role' => 'kaprodi',
        ]);

        // Dosen
        User::create([
            'name' => 'Dosen Pengampu',
            'identity' => 'dosen',
            'email' => 'dosen@unri.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
        ]);

        // Mahasiswa - Matching the login screen example
        User::create([
            'name' => 'Mahasiswa Teknik',
            'identity' => '2207111385',
            'email' => 'mahasiswa@student.unri.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);
    }
}
