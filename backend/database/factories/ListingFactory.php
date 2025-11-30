<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['active', 'closed', 'expired']);

        return [
            'seeker_user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'category_id' => Category::inRandomOrder()->first()?->id ?? 1,
            'hired_user_id' => null,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(3, true),
            'budget' => fake()->randomElement([500, 1000, 1500, 2000, 2500, 3000, 5000, 10000]),
            'status' => $status,
        ];
    }

    // Change to 'closed' instead of 'completed'
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'hired_user_id' => User::inRandomOrder()->first()?->id ?? 1,
        ]);
    }

    // Change to 'active' (active listings with hired user)
    public function activeWithHiredUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'hired_user_id' => User::inRandomOrder()->first()?->id ?? 1,
        ]);
    }

    // Add expired state
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'hired_user_id' => null,
        ]);
    }
}
