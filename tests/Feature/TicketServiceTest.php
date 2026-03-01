<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\User;
use App\Services\TicketService;

class TicketServiceTest extends TestCase
{
    // Esto asegura que la base de datos temporal se limpie entre cada test
    use RefreshDatabase;

    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        // Instanciamos el servicio que vamos a poner a prueba
        $this->ticketService = new TicketService();
    }

    /**
     * Prueba 1: Verifica que si hay 20 tickets, el servicio devuelve solo 15 en la primera página.
     */
    public function test_get_all_tickets_returns_paginated_results()
    {
        // 1. Arrange (Preparar el entorno): Creamos 20 tickets falsos en la BD en memoria
        Ticket::factory()->count(20)->create();

        // 2. Act (Actuar): Llamamos a nuestro servicio sin filtros
        $result = $this->ticketService->getAllTickets();

        // 3. Assert (Comprobar): Verificamos que devuelve 15 elementos y que el total es 20
        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Prueba 2: Verifica que el motor de búsqueda y filtros funciona correctamente.
     */
    public function test_get_all_tickets_applies_filters_correctly()
    {
        // 1. Arrange: Creamos categorías y tickets muy específicos
        $categoriaFallo = Category::factory()->create(['name' => 'Fallo Crítico']);
        $categoriaDuda = Category::factory()->create(['name' => 'Duda General']);

        // Creamos 5 tickets de "Fallo Crítico" en estado "Abierto"
        Ticket::factory()->count(5)->create([
            'category_id' => $categoriaFallo->id,
            'status' => 'Abierto',
            'title' => 'Tubería rota'
        ]);

        // Creamos 3 tickets de "Duda General" en estado "Cerrado"
        Ticket::factory()->count(3)->create([
            'category_id' => $categoriaDuda->id,
            'status' => 'Cerrado',
            'title' => 'Horario ayuntamiento'
        ]);

        // 2. Act: Le pedimos al servicio que filtre solo los Fallos Críticos Abiertos
        $filters = [
            'category_id' => $categoriaFallo->id,
            'status' => 'Abierto'
        ];
        
        $result = $this->ticketService->getAllTickets($filters);

        // 3. Assert: Debería devolvernos exactamente los 5 tickets que coinciden
        $this->assertCount(5, $result->items());
        $this->assertEquals('Abierto', $result->first()->status);
        $this->assertEquals($categoriaFallo->id, $result->first()->category_id);
    }
}