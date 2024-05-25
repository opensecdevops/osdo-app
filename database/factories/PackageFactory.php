<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repository_id' => $this->faker->unique()->numberBetween(1000, 9999),
            'user_id' => \App\Models\User::factory(), // Asumiendo que tienes un modelo User y su factory
            'service_id' => \App\Models\Service::factory(), // Asumiendo que tienes un modelo Service y su factory
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'license' => $this->faker->word(),
            'repository' => $this->faker->url(),
            'homepage' => $this->faker->url(),
            'type' => $this->faker->randomElement([1, 2]), // 1: Infra, 2: Pipeline
            'message' => 'pending', // Valor predeterminado
            'status' => 0, // Valor predeterminado
        ];
    }
}
