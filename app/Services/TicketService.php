<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    /**
     * Obtiene todos los tickets con sus relaciones.
     */
    public function getAllTickets()
    {
        return Ticket::with(['user', 'category'])->get();
    }

    /**
     * Crear un nuevo ticket en la base de datos.
     */
    public function createTicket(array $data)
    {
        return Ticket::create($data);
    }

    /**
     * Obtener un ticket específico por su ID.
     */
    public function getTicketById(string $id)
    {
        // findOrFail devuelve un error 404 automático si el ticket no existe
        return Ticket::with(['user', 'category'])->findOrFail($id);
    }

    /**
     * Actualizar un ticket existente.
     */
    public function updateTicket(string $id, array $data)
    {
        $ticket = $this->getTicketById($id);
        $ticket->update($data);
        return $ticket;
    }

    /**
     * Eliminar un ticket de la base de datos.
     */
    public function deleteTicket(string $id)
    {
        $ticket = $this->getTicketById($id);
        $ticket->delete();
        return true;
    }
}

