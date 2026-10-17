<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nama'     => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => 'password',
            'role'     => 'petugas',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function petugas(): static
    {
        return $this->state(['role' => 'petugas']);
    }
}
