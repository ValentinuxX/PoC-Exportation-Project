<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketService; // <-- Importo el servicio

class TicketController extends Controller
{
    protected $ticketService;

    // Inyecto el servicio en el constructor para usarlo en toda la clase
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        // El controlador ya no se encarga de buscar los tickets, solo se los pide al servicio
        $tickets = $this->ticketService->getAllTickets();

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        // 1. El controlador si asume la responsabilidad de Validar (Seguridad)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|string'
        ]);

        // 2. Delega la responsabilidad de Crear al servicio
        $ticket = $this->ticketService->createTicket($validated);

        // 3. Asume la responsabilidad de Responder
        return response()->json($ticket, 201);
    }

    // Dejo el resto de funciones em TODO
    public function show(string $id) {

        // Delego la búsqueda al servicio
        $ticket = $this->ticketService->getTicketById($id);
        
        return response()->json($ticket, 200);
    }

    public function update(Request $request, string $id) {

        // 1. Valido los datos (uso 'sometimes' para que solo valide lo que el usuario decida enviar)
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'status' => 'sometimes|required|string'
        ]);

        // 2. Delego la actualización al servicio
        $ticket = $this->ticketService->updateTicket($id, $validated);

        return response()->json($ticket, 200);
    }


    public function destroy(string $id) {

        // Delego el borrado al servicio
        $this->ticketService->deleteTicket($id);

        return response()->json(['message' => 'Ticket eliminado correctamente'], 200);
    }


    /**
     * Exportar los tickets a un archivo CSV aplicando los filtros recibidos.
     */
    public function export(Request $request)
    {
        // 1. Pedimos los datos al servicio, pasándole los filtros de la URL (Request)
        $tickets = $this->ticketService->getTicketsForExport($request->all());

        // 2. Preparamos las cabeceras HTTP para forzar la descarga del archivo CSV
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=tickets_export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // 3. Creamos la función "Stream" que irá escribiendo el archivo línea a línea en la memoria
        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w'); 
            
            // Añado el BOM (Byte Order Mark) de UTF-8. 
            // Esto le dice a Microsoft Excel que el archivo tiene acentos y eñes para que no rompa el texto.
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Escribo la primera fila (Las cabeceras de las columnas)
            fputcsv($file, [
                'ID', 
                'Título', 
                'Estado', 
                'Usuario', 
                'Categoría', 
                'Prioridad', 
                'Fecha de Creación'
            ]);

            // Iteramos sobre la colección y escribimos una línea por cada ticket
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->title,
                    $ticket->status ?? 'Sin estado',
                    // Uso el operador nullsafe (??) por si acaso algún ticket fue borrado de su usuario/categoría
                    $ticket->user->name ?? 'Sin usuario',
                    $ticket->category->name ?? 'Sin categoría',
                    $ticket->category->priority ?? 'N/A', // Saco la prioridad desde la relación con la categoría
                    $ticket->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file); 
        };

        // 4. Devuelvo la respuesta en streaming hacia el navegador del usuario
        return response()->stream($callback, 200, $headers);
    }
}
