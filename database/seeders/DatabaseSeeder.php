<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creo el usuario administrador
        $admin = User::factory()->create([
            'name' => 'Admin PoC',
            'email' => 'admin@test.com',
        ]);

        // 2. Creo las categorías y guardamos sus IDs
        $categorias = [
            Category::create(['name' => 'Fallo Crítico', 'priority' => 'Alta'])->id,
            Category::create(['name' => 'Soporte Técnico', 'priority' => 'Media'])->id,
            Category::create(['name' => 'Duda General', 'priority' => 'Baja'])->id,
        ];

        $estados = ['Abierto', 'Pendiente', 'Resuelto', 'Cerrado'];

        // 3. Insercción de 50.000 registros en bloques de 5.000
        $totalTickets = 50000;
        $chunkSize = 5000;
        $now = Carbon::now();

        $this->command->info("Iniciando siembra masiva de {$totalTickets} tickets en MariaDB...");

        for ($i = 0; $i < $totalTickets; $i += $chunkSize) {
            $tickets = [];
            
            for ($j = 0; $j < $chunkSize; $j++) {
                $tickets[] = [
                    'title' => 'Avería municipal ' . ($i + $j) . ' - (tubería, camión, farola)',
                    'description' => 'Reporte con caracteres especiales para probar Excel: año, camión, desagüe, pingüino, avería. Número de caso: ' . rand(1000, 9999),
                    'status' => $estados[array_rand($estados)],
                    'category_id' => $categorias[array_rand($categorias)],
                    'user_id' => $admin->id,
                    'created_at' => $now->copy()->subDays(rand(0, 365)),
                    'updated_at' => $now,
                ];
            }

            // Inserto 5000 de golpe directamente en la tabla (saltándome el ORM para ahorrar RAM)
            DB::table('tickets')->insert($tickets);
            
            $this->command->info("Insertados " . ($i + $chunkSize) . " tickets...");
        }

        $this->command->info('Base de datos sembrada con éxito.');
    }
}