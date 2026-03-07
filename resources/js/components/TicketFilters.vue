<template>
  <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
      
      <div class="lg:col-span-2">
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Buscar Incidencia</label>
        <input 
          v-model="filters.search" 
          type="text" 
          placeholder="Ej: Tubería, farola..." 
          class="w-full bg-gray-50 border border-gray-300 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Estado</label>
        <select v-model="filters.status" class="w-full bg-gray-50 border border-gray-300 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
          <option value="">Todos</option>
          <option value="Abierto">Abierto</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Resuelto">Resuelto</option>
          <option value="Cerrado">Cerrado</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Desde Fecha</label>
        <input v-model="filters.date_from" type="date" class="w-full bg-gray-50 border border-gray-300 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hasta Fecha</label>
        <input v-model="filters.date_to" type="date" class="w-full bg-gray-50 border border-gray-300 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
      </div>

    </div>
    
    <div class="mt-5 flex justify-end gap-3 border-t border-gray-100 pt-4">
      <button @click="clearFilters" class="px-5 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
        Limpiar Filtros
      </button>
      <button @click="applyFilters" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow transition flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Aplicar Filtros
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Definimos el evento que este componente emitirá hacia App.vue
const emit = defineEmits(['filter-applied']);

// Estado inicial vacío
const defaultFilters = {
  search: '',
  status: '',
  date_from: '',
  date_to: ''
};

// Variable reactiva que el usuario modifica con los inputs
const filters = ref({ ...defaultFilters });

// Envía los filtros al componente padre (App.vue)
const applyFilters = () => {
  emit('filter-applied', { ...filters.value });
};

// Resetea los campos y envía la orden de limpiar al padre
const clearFilters = () => {
  filters.value = { ...defaultFilters };
  emit('filter-applied', { ...filters.value });
};
</script>
