<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Una frase aleatoria sin punto final para el título
            'title' => fake()->sentence(4, true), 
            // Un par de párrafos falsos ("Lorem ipsum...")
            'description' => fake()->paragraph(2), 
            // Un estado al azar
            'status' => fake()->randomElement(['Abierto', 'Pendiente', 'Resuelto', 'Cerrado']), 
            
            // Si no le pasamos un ID concreto al crear el ticket, creará uno nuevo automáticamente
            'category_id' => \App\Models\Category::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
