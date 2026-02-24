<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Creo mi usuario administrador de pruebas
        $admin = User::factory()->create([
            'name' => 'Admin PoC',
            'email' => 'admin@test.com',
            // La contraseña por defecto de Laravel en los factories siempre es 'password'
        ]);

        // 2. Creao 3 categorías fijas reales para tener filtros coherentes
        $categorias = [
            Category::create(['name' => 'Fallo Crítico', 'priority' => 'Alta']),
            Category::create(['name' => 'Soporte Técnico', 'priority' => 'Media']),
            Category::create(['name' => 'Duda General', 'priority' => 'Baja']),
        ];

        // 3. Fabrico 50 tickets usando el TicketFactory
        foreach (range(1, 50) as $index) {
            Ticket::factory()->create([
                'user_id' => $admin->id,
                // Elijo una categoría aleatoria del array que cree en el paso 2
                'category_id' => $categorias[array_rand($categorias)]->id,
            ]);
        }
    }
}