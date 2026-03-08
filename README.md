# PoC Exportation Project

```text
  _____        _____   _______ _      _        _       
 |  __ \      / ____| |__   __(_)    | |      | |      
 | |__) |___ | |         | |   _  ___| | _____| |_ ___ 
 |  ___/ _ \| |         | |  | |/ __| |/ / _ \ __/ __|
 | |  | (_) | |____     | |  | | (__|   <  __/ |_\__ \
 |_|   \___/ \_____|    |_|  |_|\___|_|\_\___|\__|___/

 
# Resumen de Arquitectura Backend (Laravel API)

El backend de esta Prueba de Concepto (PoC) se ha construido bajo una arquitectura robusta, escalable y totalmente desacoplada, exponiendo una API RESTful estricta para ser consumida por el frontend (Vue 3).

## Patrones de Diseño y Estructura

* **Patrón de Servicios (Service Pattern):** La lógica de negocio y las consultas complejas a la base de datos se han extraído a clases dedicadas (ej. `TicketService`). Los controladores mantienen una "Responsabilidad Única" (SOLID), limitándose a recibir la petición HTTP, validar datos, delegar la tarea al servicio y devolver una respuesta JSON o un flujo de datos.
* **Enrutamiento API:** Las rutas están claramente separadas en el archivo `routes/api.php`, priorizando correctamente rutas estáticas (como `/export`) sobre rutas dinámicas (`apiResource`) para evitar conflictos de resolución (Errores 404).

## Rendimiento y Optimización de Datos

* **Data Streaming Seguro:** Para la exportación masiva de datos (50.000+ registros), se descartó la carga en memoria (`->get()`) para evitar colapsos de RAM (Out of Memory). En su lugar, se implementó una salida HTTP en streaming directa al navegador mediante `response()->stream()`.
* **Resolución del Problema N+1:** Para evitar que el streaming ahogara la base de datos con peticiones individuales, se combinó la lectura por bloques (`chunk(1000)`) con la precarga de relaciones (*Eager Loading* usando `with()`). Esto redujo las consultas, logrando tiempos de exportación de ~7 segundos.
* **Compatibilidad de Codificación:** Se inyectó el BOM (Byte Order Mark) UTF-8 directamente en el flujo del archivo CSV para garantizar una representación perfecta de caracteres especiales (tildes, eñes) nativa en Microsoft Excel.

---

# Resumen de Arquitectura Frontend (Vue 3 SPA)

El frontend de esta Prueba de Concepto (PoC) se ha desarrollado como una Single Page Application (SPA) moderna, enfocada en la reactividad, el rendimiento y una experiencia de usuario sin interrupciones ni recargas de página.

## Tecnologías y Ecosistema

* **Vue 3 (Composition API):** Se ha utilizado la sintaxis moderna de Vue (`<script setup>`) para lograr un código más conciso, modular y fácil de mantener.
* **Vite:** Actúa como motor de compilación y servidor de desarrollo, proporcionando Hot Module Replacement (HMR) para reflejar los cambios en el navegador de forma instantánea. Se resolvieron exitosamente los retos de infraestructura y puertos (CORS) en entornos de nube (Codespaces).
* **Tailwind CSS:** Implementado como framework de estilos basado en utilidades, permitiendo una maquetación ágil, consistente y con un peso de archivo final mínimo gracias a la purga de clases no utilizadas.

## Estructura de Componentes y Estado Reactivo

* **Desacoplamiento Visual:** La interfaz se ha dividido en componentes modulares. El componente principal gestiona el estado global de la vista (la tabla de datos y la paginación), mientras que subcomponentes dedicados, como `TicketFilters.vue`, encapsulan la lógica de la interfaz de usuario específica.
* **Comunicación entre Componentes:** Se ha implementado un flujo de datos unidireccional estructurado. Los componentes hijos recolectan la interacción del usuario y utilizan eventos (`emit`) para notificar al componente padre, quien centraliza la actualización del estado y la comunicación con el backend.
* **Gestión de Estado:** Uso de referencias reactivas (`ref`) para controlar los datos de la tabla, los criterios de filtrado y los estados de interfaz (como los indicadores de "Cargando datos...").

## Integración con la API y Experiencia de Usuario

* **Peticiones Dinámicas:** La aplicación construye dinámicamente las cadenas de consulta (Query Strings) utilizando `URLSearchParams`, iterando sobre los filtros activos (búsqueda por texto, estado, fechas) y omitiendo valores vacíos para mantener URLs limpias hacia la API de Laravel.
* **Paginación Reactiva:** La navegación entre miles de registros se realiza de forma asíncrona, actualizando únicamente el bloque de datos de la tabla y manteniendo la coherencia con los filtros aplicados en todo momento.
* **Exportación Integrada (CSV):** La funcionalidad de descarga masiva se ha conectado de forma transparente. En lugar de procesar grandes volúmenes de datos en la memoria del navegador, el frontend delega el trabajo pesado reconstruyendo la URL con los filtros activos y redirigiendo silenciosamente (`window.location.href`) para interceptar el archivo en streaming generado por el backend.

---

