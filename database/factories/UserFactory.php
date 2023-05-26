<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     * 
     * @var string
     protection $model = User::class;
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'users_name' => $this->faker->name(),
            'users_email' => $this->faker->unique()->safeEmail(),
            'users_role' => 'user',
            'users_password' => 'user',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
