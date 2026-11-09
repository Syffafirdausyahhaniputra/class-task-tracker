<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'Password123',
            'role' => 'student',
            'class_id' => null,
        ];
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher',
            'class_id' => null,
        ]);
    }

    public function studentIn(SchoolClass $schoolClass): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
            'class_id' => $schoolClass->id,
        ]);
    }
}
