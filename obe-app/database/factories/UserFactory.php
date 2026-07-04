<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
            'identity'           => fake()->unique()->numerify('##########'), // NIP/NIM 10 digit
            'initials'           => strtoupper(Str::random(3)),
            'role'               => 'mahasiswa',
            'jabatan_akademik'   => null,
            'jurusan_id'         => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** State: user sebagai dosen */
    public function dosen(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'             => 'dosen',
            'jabatan_akademik' => 'dosen',
            'identity'         => fake()->unique()->numerify('19##########'),
        ]);
    }

    /** State: user sebagai kaprodi */
    public function kaprodi(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'             => 'kaprodi',
            'jabatan_akademik' => 'dosen',
            'identity'         => fake()->unique()->numerify('19##########'),
        ]);
    }

    /** State: user sebagai admin */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'     => 'admin',
            'identity' => fake()->unique()->numerify('ADM#######'),
        ]);
    }

    /** State: user sebagai mahasiswa */
    public function mahasiswa(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'     => 'mahasiswa',
            'identity' => fake()->unique()->numerify('20##########'),
        ]);
    }
}