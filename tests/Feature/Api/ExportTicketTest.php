<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ExportTicketTest extends TestCase
{
    use RefreshDatabase;

    // Declaro las variables para poder usarlas en todos los tests
    protected $user;
    protected $catAlta;
    protected $catBaja;
    protected $ticketNuevoAbierto;
    protected $ticketViejoCerrado;
    protected $ticketAyerPendiente;

    /**
     * FASE 1: PREPARACIÓN DEL LABORATORIO
     * Este método se ejecuta automáticamente antes de CADA test.
     */
    
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Creo dependencias base
        $this->user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => '123456']);
        $this->catAlta = Category::create(['name' => 'Fallo Crítico', 'priority' => 'Alta']);
        $this->catBaja = Category::create(['name' => 'Duda', 'priority' => 'Baja']);

        // 2. Creo los tickets normales (Laravel les pondrá la fecha de hoy por defecto)
        
        // Ticket A: Nuevo
        $this->ticketNuevoAbierto = Ticket::create([
            'title' => 'Error al iniciar sesión',
            'description' => 'No me deja entrar',
            'category_id' => $this->catAlta->id,
            'user_id' => $this->user->id,
            'status' => 'Abierto',
        ]);

        // Ticket B: Viejo
        $this->ticketViejoCerrado = Ticket::create([
            'title' => 'Consulta sobre facturación',
            'description' => 'Quiero mi factura',
            'category_id' => $this->catBaja->id,
            'user_id' => $this->user->id,
            'status' => 'Cerrado',
        ]);
        // Voy directo a la base de datos para cambiar la fecha y saltarme a Laravel
        DB::table('tickets')->where('id', $this->ticketViejoCerrado->id)->update(['created_at' => now()->subMonths(2)]);

        // Ticket C: Medio
        $this->ticketAyerPendiente = Ticket::create([
            'title' => 'Pantalla en blanco',
            'description' => 'Todo se ve blanco',
            'category_id' => $this->catAlta->id,
            'user_id' => $this->user->id,
            'status' => 'Pendiente',
        ]);
        // Lo muevo al día de ayer
        DB::table('tickets')->where('id', $this->ticketAyerPendiente->id)->update(['created_at' => now()->subDays(1)]);
    }

    /**
     * FASE 2: TEST ESTRUCTURAL
     * Compruebo que el archivo está bien construido (BOM, Cabeceras, Columnas)
     */
    public function test_exportacion_devuelve_formato_csv_valido_con_bom()
    {
        // ACCIÓN: Hago la petición sin filtros (debería devolver los 3 tickets)
        $response = $this->get('/api/tickets/export');

        // VERIFICACIÓN:
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contenido = $response->streamedContent();

        // 1. Verifico que tiene el BOM de UTF-8 al principio del archivo para Excel
        $this->assertStringStartsWith(chr(0xEF) . chr(0xBB) . chr(0xBF), $contenido);

        // 2. Verifico que las cabeceras de las columnas existen
        $this->assertStringContainsString('ID,Título,Estado,Usuario,Categoría,Prioridad,"Fecha de Creación"', $contenido);

        // 3. Verifico que al menos el título de uno de los tickets está en el CSV
        $this->assertStringContainsString('Error al iniciar sesión', $contenido);
    }

    /**
     * TESTS AISLADOS 
     */

    public function test_exportacion_filtra_correctamente_por_estado()
    {
        // ACCIÓN: Pedir SOLO los tickets 'Abierto'
        $response = $this->get('/api/tickets/export?status=Abierto');
        $response->assertStatus(200);

        $contenido = $response->streamedContent();

        // VERIFICACIÓN:
        $this->assertStringContainsString('Error al iniciar sesión', $contenido); // Es Abierto (Debe estar)
        $this->assertStringNotContainsString('Consulta sobre facturación', $contenido); // Es Cerrado (NO debe estar)
        $this->assertStringNotContainsString('Pantalla en blanco', $contenido); // Es Pendiente (NO debe estar)
    }

    public function test_exportacion_filtra_correctamente_por_busqueda_de_texto()
    {
        // ACCIÓN: Buscar la palabra "Pantalla" en el título
        $response = $this->get('/api/tickets/export?search=Pantalla');
        $response->assertStatus(200);

        $contenido = $response->streamedContent();

        // VERIFICACIÓN:
        $this->assertStringContainsString('Pantalla en blanco', $contenido); // Contiene "Pantalla"
        $this->assertStringNotContainsString('Error al iniciar sesión', $contenido); // NO lo contiene
    }

    public function test_exportacion_filtra_correctamente_por_rango_de_fechas()
    {
        // PREPARACIÓN: Definir el rango desde ayer hasta hoy
        $ayer = now()->subDays(1)->format('Y-m-d');
        $hoy = now()->format('Y-m-d');

        // ACCIÓN: Filtrar por fecha
        $response = $this->get("/api/tickets/export?date_from={$ayer}&date_to={$hoy}");
        $response->assertStatus(200);

        $contenido = $response->streamedContent();

        // VERIFICACIÓN:
        $this->assertStringContainsString('Error al iniciar sesión', $contenido); // Es de hoy (Debe estar)
        $this->assertStringContainsString('Pantalla en blanco', $contenido); // Es de ayer (Debe estar)
        $this->assertStringNotContainsString('Consulta sobre facturación', $contenido); // Es de hace 2 meses (NO debe estar)
    }

    public function test_exportacion_filtra_correctamente_por_prioridad_relacional()
    {
        // ACCIÓN: Pedir tickets con prioridad 'Baja' (Recuerda que esto busca en la tabla Categorías)
        $response = $this->get('/api/tickets/export?priority=Baja');
        $response->assertStatus(200);

        $contenido = $response->streamedContent();

        // VERIFICACIÓN:
        $this->assertStringContainsString('Consulta sobre facturación', $contenido); // Categoría Baja
        $this->assertStringNotContainsString('Error al iniciar sesión', $contenido); // Categoría Alta
    }

    /**
     * FASE 4: Los filtros combinados
     * Verifico que los filtros se suman con condiciones AND.
     */
    public function test_exportacion_aplica_multiples_filtros_simultaneamente()
    {
        // ACCIÓN: Busco tickets que cumplan TODO esto a la vez:
        // - Prioridad: Alta
        // - Estado: Abierto
        // - Creado: Hoy
        // - Texto: "Error"
        
        $hoy = now()->format('Y-m-d');
        
        $queryString = http_build_query([
            'priority' => 'Alta',
            'status' => 'Abierto',
            'date_from' => $hoy,
            'search' => 'Error'
        ]);

        $response = $this->get('/api/tickets/export?' . $queryString);
        $response->assertStatus(200);

        $contenido = $response->streamedContent();

        // VERIFICACIÓN:
        // 1. El Ticket A ("Error al iniciar sesión") cumple absolutamente todo. DEBE estar.
        $this->assertStringContainsString('Error al iniciar sesión', $contenido); 
        
        // 2. El Ticket B ("Consulta sobre facturación") falla en prioridad, estado, fecha y texto. NO debe estar.
        $this->assertStringNotContainsString('Consulta sobre facturación', $contenido); 
        
        // 3. El Ticket C ("Pantalla en blanco") es prioridad Alta, pero falla en estado, fecha y texto. NO debe estar.
        $this->assertStringNotContainsString('Pantalla en blanco', $contenido); 
    }
}
