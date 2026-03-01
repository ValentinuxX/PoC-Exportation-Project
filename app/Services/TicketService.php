<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    /**
     * MÉTODO PRiVADO: Construye la consulta base con los filtros.
     * Así no repetimos este código gigante en la paginación y en la exportación.
     */
    private function buildFilterQuery(array $filters = [])
    {
        $query = Ticket::with(['user', 'category']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('priority', $filters['priority']);
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }

    /**
     * Obtiene los tickets filtrados y PAGINADOS (Para la tabla de Vue.js)
     */
    public function getAllTickets(array $filters = [])
    {
        // Usamos la consulta base y le aplicamos paginate(15)
        return $this->buildFilterQuery($filters)
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
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
     * Obtener los tickets filtrados para exportación.
     * NOTA: Solo devolvemos la query para que el ExportService pueda usar ->cursor() y hacer streaming.
     */
    public function getTicketsForExport(array $filters = [])
    {
        return $this->buildFilterQuery($filters)->orderBy('created_at', 'desc');
    }
}