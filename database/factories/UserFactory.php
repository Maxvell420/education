<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => "velichko",
            'email' => "sasha@sobaka.com",
            'role_id'=>2,
            'password' => Hash::make("12345"),
        ];
    }
    public function bot()
    {
        return $this->state(function (array $attributes){
            return [
                'name'=>'telegram_bot',
                'role_id'=>3,
            ];
        });
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
