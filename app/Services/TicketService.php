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

    /**
     * Obtener los tickets filtrados por múltiples criterios.
     */
    public function getTicketsForExport(array $filters = [])
    {
        // Inicio la consulta base
        $query = Ticket::with(['user', 'category']);

        // 1. Filtro por Estado (Exacto)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

       // 2. Filtro por Prioridad (Buscando dentro de la tabla categorías)
        if (!empty($filters['priority'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('priority', $filters['priority']);
            });
        }

        // 3. Filtro por Categoría (ID exacto)
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // 4. Filtro por Usuario (ID exacto)
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // 5. Filtro por Rango de Fechas (Creación)
        if (!empty($filters['date_from'])) {
            // "Desde" las 00:00:00 de ese día
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            // "Hasta" las 23:59:59 de ese día
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // 6. Búsqueda por texto (Título) - "LIKE" es para búsquedas parciales
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        // Ordenamos por los más nuevos primero
        return $query->orderBy('created_at', 'desc')->get();
    }
}

