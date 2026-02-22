<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;

class TicketApiTest extends TestCase
{
    
    // Antes de cada test, ejecuta las migraciones en la memoria RAM 
    // y cuando termine el test, borra todo para dejarla limpia".
    use RefreshDatabase; 

    public function test_puede_obtener_todos_los_tickets()
    {
        // 1. PREPARACIÓN (Arrange): Crea datos falsos en la RAM
        $user = User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => '123456']);
        $category = Category::create(['name' => 'Fallo', 'priority' => 'Alta']);
        Ticket::create([
            'title' => 'Ticket de prueba GET',
            'description' => 'Descripción de prueba',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'status' => 'Pendiente'
        ]);

        // 2. ACCIÓN (Act): El robot hace un GET a mi API (como en Postman)
        $response = $this->getJson('/api/tickets');

        // 3. VERIFICACIÓN (Assert): Comprobamos que devuelve un 200 OK y trae el ticket
        $response->assertStatus(200)
                 ->assertJsonCount(1) // Comprueba que hay 1 ticket en la lista
                 ->assertJsonFragment(['title' => 'Ticket de prueba GET']);
    }

    public function test_puede_crear_un_ticket_valido()
    {
        // 1. PREPARACIÓN
        $user = User::create(['name' => 'Test User', 'email' => 'test2@test.com', 'password' => '123456']);
        $category = Category::create(['name' => 'Duda', 'priority' => 'Baja']);

        $datosPayload = [
            'title' => 'Ticket desde el Test POST',
            'description' => 'Validando el insert automático',
            'category_id' => $category->id,
            'user_id' => $user->id,
            'status' => 'Abierto'
        ];

        // 2. ACCIÓN: El robot hace un POST enviando el JSON
        $response = $this->postJson('/api/tickets', $datosPayload);

        // 3. VERIFICACIÓN: Comprueba el 201 Created y que el ticket exista físicamente
        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Ticket desde el Test POST']);
                 
        $this->assertDatabaseHas('tickets', [
            'title' => 'Ticket desde el Test POST'
        ]);
    }

    public function test_el_escudo_bloquea_tickets_con_categoria_inventada()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test3@test.com', 'password' => '123456']);

        $datosMalos = [
            'title' => 'Ticket Hacker',
            'description' => 'Intento saltarme la validación',
            'category_id' => 9999, // ID QUE NO EXISTE
            'user_id' => $user->id
        ];

        $response = $this->postJson('/api/tickets', $datosMalos);

        // Verifico que devuelve 422 y que el error es por culpa de 'category_id'
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['category_id']);
    }
}
