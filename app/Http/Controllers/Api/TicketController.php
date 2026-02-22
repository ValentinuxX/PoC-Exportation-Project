<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Traigo todos los tickets incuyendo los datos del usuario y la categoría en la misma consulta
        $tickets = Ticket::with(['user', 'category'])->get();

        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valido los datos de entrada
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id', // Debe existir
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|string'
        ]);

        // Crear el ticket (usando el $fillable del modelo)
        $ticket = Ticket::create($validated);

        // Devuelvo el ticket recién creado con un código de estado HTTP 201
        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
