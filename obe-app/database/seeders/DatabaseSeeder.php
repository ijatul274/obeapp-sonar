<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::factory()->admin()->create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'identity' => 'ADM0000001',
        ]);

        // Kaprodi
        User::factory()->kaprodi()->create([
            'name'     => 'Kaprodi Test',
            'email'    => 'kaprodi@example.com',
            'identity' => '1990000001',
            'initials' => 'KPR',
        ]);

        // Dosen
        User::factory()->dosen()->create([
            'name'     => 'Dosen Test',
            'email'    => 'dosen@example.com',
            'identity' => '1990000002',
            'initials' => 'DSN',
        ]);

        // Mahasiswa
        User::factory()->mahasiswa()->create([
            'name'     => 'Mahasiswa Test',
            'email'    => 'mahasiswa@example.com',
            'identity' => '2021000001',
        ]);
    }
}