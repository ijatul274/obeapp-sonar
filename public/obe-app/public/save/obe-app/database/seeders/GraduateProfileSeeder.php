<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GraduateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Artificial Intelligence Engineer',
                'description' => 'Merancang, membangun, mengimplementasikan, dan memelihara sistem kecerdasan buatan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'System Analyst',
                'description' => 'Spesialis analisa kebutuhan industri, merancang solusi sistem informasi yang efisien.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'IT Mobility dan Internet of Things',
                'description' => 'Perancangan aplikasi mobile dan perangkat IoT untuk solusi cerdas.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Programmer dan Software Developer',
                'description' => 'Merancang dan mengembangkan perangkat lunak sesuai kebutuhan pengguna.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('graduate_profiles')->insert($profiles);
    }
}
