<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      
      <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Gestor de Incidencias</h1>
          <p class="text-gray-500 text-sm mt-1">
            Mostrando página {{ tickets.current_page || 1 }} de {{ tickets.last_page || 1 }} 
            (Total: {{ tickets.total || 0 }} registros)
          </p>
        </div>
        <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-5 rounded-lg shadow transition flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Exportar Excel (CSV)
        </button>
      </div>

      <TicketFilters @filter-applied="updateFiltersAndFetch" />

      <div v-if="loading" class="flex justify-center items-center py-20 bg-white rounded-xl shadow border border-gray-200">
        <div class="text-xl text-blue-600 font-semibold animate-pulse flex items-center gap-3">
          Cargando datos...
        </div>
      </div>

      <div v-else class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">ID</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Título</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Estado</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Categoría</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-blue-50 transition duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">#{{ ticket.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ ticket.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="{
                    'bg-green-100 text-green-800': ticket.status === 'Resuelto',
                    'bg-red-100 text-red-800': ticket.status === 'Abierto',
                    'bg-yellow-100 text-yellow-800': ticket.status === 'Pendiente',
                    'bg-gray-100 text-gray-800': ticket.status === 'Cerrado'
                  }" class="px-3 py-1 inline-flex text-xs font-semibold rounded-full">
                    {{ ticket.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                  {{ ticket.category ? ticket.category.name : 'Sin categoría' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ new Date(ticket.created_at).toLocaleDateString('es-ES') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <button @click="fetchTickets(tickets.current_page - 1)" :disabled="tickets.current_page === 1" class="bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-4 rounded shadow-sm hover:bg-gray-50 disabled:opacity-50 transition">Anterior</button>
          <span class="text-sm text-gray-600">Página <span class="font-bold">{{ tickets.current_page }}</span> de {{ tickets.last_page }}</span>
          <button @click="fetchTickets(tickets.current_page + 1)" :disabled="tickets.current_page === tickets.last_page" class="bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-4 rounded shadow-sm hover:bg-gray-50 disabled:opacity-50 transition">Siguiente</button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import TicketFilters from './components/TicketFilters.vue'; // Importamos el componente

const tickets = ref({ data: [], current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);
const activeFilters = ref({}); // Aquí guardaremos los filtros que nos envíe el componente

// Esta función se dispara cuando el usuario pulsa "Aplicar Filtros" en el componente hijo
const updateFiltersAndFetch = (newFilters) => {
  activeFilters.value = newFilters;
  fetchTickets(1); // Siempre que filtramos, volvemos a la página 1
};

const fetchTickets = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({ page: page });

    // Recorremos los filtros activos y los añadimos a la URL si tienen algún valor
    Object.keys(activeFilters.value).forEach(key => {
      if (activeFilters.value[key] !== '') {
        params.append(key, activeFilters.value[key]);
      }
    });

    const response = await fetch(`/api/tickets?${params.toString()}`);
    const data = await response.json();
    tickets.value = data; 
  } catch (error) {
    console.error("Error cargando los tickets:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchTickets();
});
</script>
