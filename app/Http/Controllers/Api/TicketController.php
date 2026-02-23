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
}
