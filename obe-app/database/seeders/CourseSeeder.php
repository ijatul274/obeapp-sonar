<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have some lecturers (dosen)
        // Check if lecturer exists, otherwise create or use existing users
        // For simulation, we will try to find users with role 'dosen' or create them
        
        $lecturers = [
            'Reny Fitri Yani, S.T., M.T.',
            'Ery Safrianti, S.T., M.T.',
            'Ririn Violina',
            'T Yudi Hadiwandra, S.Kom., M.Kom.',
            'Khairul Umam Syaliman, S.T., M.Kom',
            'Noveri Lysbetti Marpaung, ST., M.Sc.'
        ];

        foreach ($lecturers as $name) {
            User::firstOrCreate(
                ['email' => strtolower(str_replace([' ', '.', ','], '', $name)) . '@example.com'],
                [
                    'name' => $name,
                    'identity' => rand(10000000, 99999999), // Dummy NIP
                    'password' => '$2y$12$m3WNr8J5FgaSj73Q6Qd6z.GBtuwQ89HbfctHwgH4HIsKuzcID96Za', // password
                    'role' => 'dosen'
                ]
            );
        }

        $courses = [
            [
                'code' => 'TIK 07111001',
                'name' => 'Pengenalan Pemrograman',
                'sks' => 3,
                'semester' => 1,
                'lecturers' => ['Reny Fitri Yani, S.T., M.T.']
            ],
            [
                'code' => 'TIK 07111002',
                'name' => 'Kalkulus',
                'sks' => 3,
                'semester' => 1,
                'lecturers' => ['Ery Safrianti, S.T., M.T.', 'Ririn Violina']
            ],
            [
                'code' => 'TIK 07111003',
                'name' => 'Statistika',
                'sks' => 3,
                'semester' => 1,
                'lecturers' => ['T Yudi Hadiwandra, S.Kom., M.Kom.']
            ],
            [
                'code' => 'TIK 07111004', // Adjusted code to be unique
                'name' => 'Logika Matematika',
                'sks' => 4,
                'semester' => 1,
                'lecturers' => ['Khairul Umam Syaliman, S.T., M.Kom', 'Noveri Lysbetti Marpaung, ST., M.Sc.']
            ],
        ];

        foreach ($courses as $data) {
            $course = Course::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'sks' => $data['sks'],
                'semester' => $data['semester']
            ]);

            foreach ($data['lecturers'] as $lecturerName) {
                $lecturer = User::where('name', $lecturerName)->first();
                if ($lecturer) {
                    $course->users()->attach($lecturer->id);
                }
            }
        }
    }
}
