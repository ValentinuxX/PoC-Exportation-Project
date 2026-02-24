<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Invento una palabra para el nombre de la categoría
            'name' => fake()->unique()->word(), 
            // Elijo una prioridad al azar de esta lista
            'priority' => fake()->randomElement(['Alta', 'Media', 'Baja']),
        ];
    }
}
